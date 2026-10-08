## Callback Processing

Paysera sends a signed server-to-server callback (notification) to your configured callback URL when
a relevant event occurs. This is a server-to-server notification — not the browser return/redirect URL.

The SDK exposes a single entry point, `Callbacks::processCallback()`, which:

1. verifies the callback signature;
2. decodes the request body;
3. reads the event the callback carries;
4. returns a typed, validated callback object — or an `UnsupportedCallback` when no handler is
   registered for that event.

## Security

All callbacks are signed by Paysera using an HMAC signature. `processCallback()` verifies this signature
automatically and throws `CallbackVerificationIntegrationException` if it does not match. Never process
callback data without a successful verification.

## Supported events

`processCallback()` returns an object implementing `CallbackInterface`. The concrete type identifies the event:

| Event (`type:name`)        | Returned object             | Meaning                                                    |
|----------------------------|-----------------------------|------------------------------------------------------------|
| `order:amount_paid_updated`| `OrderAmountPaidCallback`   | The order's paid amount changed.                            |
| any other                  | `UnsupportedCallback`       | The SDK has no handler for this event. Acknowledge with `200`, do not process. |

An unsupported event is **not** an error and is **not** thrown: Paysera emits event types independently
of SDK releases, so receiving one the SDK does not handle is expected. Distinguish it with `instanceof`
and acknowledge it; consumers only need to handle the concrete types they care about.

Support for a new event type ships in a future SDK release — the handler list is fixed inside the SDK
and there is currently no public API for an integration to register its own. Until then,
`UnsupportedCallback::getPayload()` gives you the decoded body, so an integration that already knows
what to do with an event can act on it (or store it) instead of discarding it. That payload has passed
signature verification but has **not** been normalized or validated by the SDK — treat it as raw input.

A **malformed** payload is a different case. If the body carries no `event` object, or `event.type` /
`event.name` is missing or empty, the callback cannot be read at all and `processCallback()` throws
`CallbackBuildIntegrationException` (`400`). That is deliberate — a broken or truncated delivery must be
reported as such rather than silently acknowledged as "some future event".

## Usage

```php
<?php

use Paysera\CheckoutSdk\SdkFacade;
use Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback;
use Paysera\CheckoutSdk\Exception\CallbackVerificationIntegrationException;
use Paysera\CheckoutSdk\Exception\CallbackBuildIntegrationException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @var SdkFacade $sdkFacade
 * @var RequestInterface $request
 * @var ResponseInterface $response
 */
$callbacksFacade = $sdkFacade->getCallbacksFacade();

try {
    // Verifies the signature, decodes the body and returns a validated callback object.
    $callback = $callbacksFacade->processCallback($request);

    if ($callback instanceof OrderAmountPaidCallback) {
        $order = $callback->getOrder();
        $orderInfo = $order->getOrderInfo();

        // Business decision: is the order fully paid? (see "Determining payment completion")
        if ($orderInfo->getAmountPaid() >= $orderInfo->getAmount()) {
            $merchantOrderId = $orderInfo->getMerchantOrderId();
            $currency = $orderInfo->getCurrency();

            foreach ($order->getPaymentLinkCollection() as $paymentLink) {
                foreach ($paymentLink->getPayments() as $payment) {
                    $method = $payment->getPaymentInfo()->getMethod();
                }
            }

            // Mark the order as paid, send confirmation, etc.
        }
    }

    // Anything else is an event this SDK version does not handle: acknowledge it and do not process it.
    return $response->withStatus(200);
} catch (CallbackVerificationIntegrationException $exception) {
    // Signature verification failed.
    error_log('Callback verification failed: ' . $exception->getMessage());

    return $response->withStatus(401);
} catch (CallbackBuildIntegrationException $exception) {
    // The payload is signed but could not be decoded or failed validation.
    error_log('Callback build failed: ' . $exception->getMessage());

    return $response->withStatus(400);
}
```

To log which event was ignored, replace the comment above the `return` with an explicit branch.
`getEvent()` is typed as `CallbackEventInterface`, which exposes `getName()` and `getType()`:

```php
use Paysera\CheckoutSdk\Entity\UnsupportedCallback;

// ... inside the same try block, after the OrderAmountPaidCallback branch:
if ($callback instanceof UnsupportedCallback) {
    $event = $callback->getEvent();

    error_log(sprintf('Ignoring unsupported callback event: %s:%s', $event->getType(), $event->getName()));

    // $callback->getPayload() holds the decoded body if you want to store or forward it.
}

return $response->withStatus(200);
```

## Exceptions and HTTP responses

Both exceptions extend `IntegrationException`. `UnsupportedCallbackIntegrationException` still exists but
is deprecated and never thrown; see "Migrating from 2.x".

| Exception                                  | When                                          | Suggested HTTP status |
|--------------------------------------------|-----------------------------------------------|-----------------------|
| `CallbackVerificationIntegrationException` | HMAC signature does not match.                | `401 Unauthorized`    |
| `CallbackBuildIntegrationException`        | Body cannot be decoded, carries no readable event, or fails validation. | `400 Bad Request` |

An unsupported event produces no exception: return `200` from the branch that handles "not a callback
type I care about". Returning `200` there prevents Paysera from retrying a callback the SDK cannot handle.

## Migrating from 2.x

`processCallback()` used to throw `UnsupportedCallbackIntegrationException` for an event with no
handler. It no longer does, and the exception class is deprecated. Move the branch out of the catch:

```php
// Before (2.x)
try {
    $callback = $callbacksFacade->processCallback($request);
    // ...
} catch (UnsupportedCallbackIntegrationException $exception) {
    return $response->withStatus(200);
}

// After (3.0)
$callback = $callbacksFacade->processCallback($request);

if (!$callback instanceof OrderAmountPaidCallback) {
    return $response->withStatus(200);
}
```

If you pass the result of `processCallback()` into anything typed as `OrderAmountPaidCallback` — a
typed parameter, property, or framework argument resolver — add that `instanceof` guard before handing
the value on, or you will get a `TypeError` where you previously got an exception.

## Determining payment completion

The SDK reports *what* the callback is (the typed object) and *the order data*; deciding whether the order
is fully paid is your responsibility. For `OrderAmountPaidCallback`, compare the order amounts:

```php
$orderInfo = $callback->getOrder()->getOrderInfo();
$isFullyPaid = $orderInfo->getAmountPaid() >= $orderInfo->getAmount();
$status = $orderInfo->getStatus(); // raw status string from Paysera
```

### Payer-set orders

When the payer entered the amount on the payment page, `getOrder()->isPayerSetsAmount()` is `true`
and `getOrderInfo()->getAmount()` is the amount the payer entered, so the comparison above works
unchanged. The limits the payer could choose from are on the order:

```php
$order = $callback->getOrder();
if ($order->isPayerSetsAmount()) {
    $entered = $order->getOrderInfo()->getAmount(); // e.g. 2500 = 25.00, entered by the payer
    $minimum = $order->getMinimumAmount();         // e.g. 500
    $maximum = $order->getMaximumAmount();         // e.g. 50000
}
```

Callbacks sent before Paysera added these fields carry none of them; the SDK then reports a
fixed-amount order (`false`, `null`, `null`) and still validates the callback.

## Available data

Every callback implements `CallbackInterface`, which exposes only:

- `getEvent(): CallbackEventInterface` — `getName()` and `getType()`.

Order-specific data lives on the concrete `OrderAmountPaidCallback` and is available after an `instanceof` check:

- `getOrder(): Order`:
  - `getId(): string` — Paysera order id.
  - `isPayerSetsAmount(): bool`, `getMinimumAmount(): ?int`, `getMaximumAmount(): ?int` — whether the payer entered the amount, and the limits (minor units, `null` for a fixed-amount order).
  - `getOrderInfo(): OrderInfo` — `getMerchantOrderId()`, `getSource()`, `getAmount()`, `getAmountPaid()`, `getCurrency()`, `getStatus()`.
  - `getMerchantData(): MerchantData` — `getData(): array` (your custom reference data).
  - `getPaymentLinkCollection(): PaymentLinkCollection` — iterable of `PaymentLink` (`getId()`, `getName()`, `isPayerSetsAmount()`, `getPayments()`); each `Payment` exposes `getPaymentInfo()`, `getPurchaseInfo()`, `getPayerInfo()`.

`UnsupportedCallback` exposes `getEvent()` and `getPayload(): array`. There is no *validated* object
behind an event the SDK does not model, so `getPayload()` hands back the decoded body as-is.

## Best practices

1. **Verify before processing** — always go through `processCallback()`; never trust unverified data.
2. **Acknowledge unknown events** — check `instanceof OrderAmountPaidCallback` and return `200` for anything else.
3. **Idempotency** — process each order id only once; store it to prevent duplicate processing.
4. **Correct status codes** — `200` success/unsupported, `401` verification failure, `400` invalid payload.
5. **Respond quickly** — defer heavy work (emails, DB writes) to background processing where possible.
