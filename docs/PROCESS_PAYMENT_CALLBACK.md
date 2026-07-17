## Callback Processing

Paysera sends a signed server-to-server callback (notification) to your configured callback URL when
a relevant event occurs. This is a server-to-server notification — not the browser return/redirect URL.

The SDK exposes a single entry point, `Callbacks::processCallback()`, which:

1. verifies the callback signature;
2. decodes the request body;
3. resolves the event to a supported handler;
4. returns a typed, validated callback object.

## Security

All callbacks are signed by Paysera using an HMAC signature. `processCallback()` verifies this signature
automatically and throws `CallbackVerificationIntegrationException` if it does not match. Never process
callback data without a successful verification.

## Supported events

`processCallback()` returns an object implementing `CallbackInterface`. The concrete type identifies the event:

| Event (`type:name`)        | Returned object             | Meaning                          |
|----------------------------|-----------------------------|----------------------------------|
| `order:amount_paid_updated`| `OrderAmountPaidCallback`   | The order's paid amount changed. |

Any event the SDK does not support raises `UnsupportedCallbackIntegrationException`. New event types are
added by registering additional handlers; consumers only need to handle the concrete types they care about.

## Usage

```php
<?php

use Paysera\CheckoutSdk\SdkFacade;
use Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback;
use Paysera\CheckoutSdk\Exception\CallbackVerificationIntegrationException;
use Paysera\CheckoutSdk\Exception\CallbackBuildIntegrationException;
use Paysera\CheckoutSdk\Exception\UnsupportedCallbackIntegrationException;
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

    return $response->withStatus(200);
} catch (UnsupportedCallbackIntegrationException $exception) {
    // Unknown/unsupported event — acknowledge with 200 so it is not retried, but do not process it.
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

## Exceptions and HTTP responses

All three exceptions extend `IntegrationException`; catch them in the order shown above (most specific first).

| Exception                                  | When                                          | Suggested HTTP status |
|--------------------------------------------|-----------------------------------------------|-----------------------|
| `CallbackVerificationIntegrationException` | HMAC signature does not match.                | `401 Unauthorized`    |
| `CallbackBuildIntegrationException`        | Body cannot be decoded or fails validation.   | `400 Bad Request`     |
| `UnsupportedCallbackIntegrationException`  | Event type/name is not supported by the SDK.  | `200 OK` (do not process) |

Returning `200` for an unsupported event prevents Paysera from retrying a callback the SDK cannot handle.

## Determining payment completion

The SDK reports *what* the callback is (the typed object) and *the order data*; deciding whether the order
is fully paid is your responsibility. For `OrderAmountPaidCallback`, compare the order amounts:

```php
$orderInfo = $callback->getOrder()->getOrderInfo();
$isFullyPaid = $orderInfo->getAmountPaid() >= $orderInfo->getAmount();
$status = $orderInfo->getStatus(); // raw status string from Paysera
```

## Available data

Every callback implements `CallbackInterface`, which exposes only:

- `getEvent(): CallbackEventInterface` — `getName()` and `getType()`.

Order-specific data lives on the concrete `OrderAmountPaidCallback` and is available after an `instanceof` check:

- `getOrder(): Order`:
  - `getId(): string` — Paysera order id.
  - `getOrderInfo(): OrderInfo` — `getMerchantOrderId()`, `getSource()`, `getAmount()`, `getAmountPaid()`, `getCurrency()`, `getStatus()`.
  - `getMerchantData(): MerchantData` — `getData(): array` (your custom reference data).
  - `getPaymentLinkCollection(): PaymentLinkCollection` — iterable of `PaymentLink` (`getId()`, `getName()`, `getPayments()`); each `Payment` exposes `getPaymentInfo()`, `getPurchaseInfo()`, `getPayerInfo()`.

## Best practices

1. **Verify before processing** — always go through `processCallback()`; never trust unverified data.
2. **Acknowledge unknown events** — catch `UnsupportedCallbackIntegrationException` and return `200`.
3. **Idempotency** — process each order id only once; store it to prevent duplicate processing.
4. **Correct status codes** — `200` success/unsupported, `401` verification failure, `400` invalid payload.
5. **Respond quickly** — defer heavy work (emails, DB writes) to background processing where possible.
