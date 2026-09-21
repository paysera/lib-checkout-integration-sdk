## Payment URL
After successful payment order and link initialization, checkout will respond with a payment URL.
Use this URL to interact with the Paysera checkout payment page.

## Building the order request

There are two ways to build the payment order create request.

### Typed order-request construction (recommended)

Use `buildPaymentOrderCreateRequestFromValues()` with typed value objects. Compared to the raw-array
path below, it validates the `source` value against a fixed allow-list and, because it consumes a
typed `Metadata` object, prevents misspelled metadata keys (e.g. `referrer` instead of `referer`)
from silently leaking to the wire as custom metadata.

```php
<?php

use Paysera\CheckoutSdk\SdkFacade;
use Paysera\CheckoutSdk\Entity\Metadata;
use Paysera\CheckoutSdk\Entity\PaymentOrderSource;
use Paysera\CheckoutSdk\Entity\PaymentOrder\Purchase;
use Paysera\CheckoutSdk\Entity\PaymentOrder\RedirectUrls;

/** @var SdkFacade $sdkFacade */
$paymentsFacade = $sdkFacade->getPaymentsFacade();

$paymentOrderRequest = $paymentsFacade->buildPaymentOrderCreateRequestFromValues(
    new Purchase('CMS-ORDER-ID', 14999, 'EUR'), // amount in cents or minor units
    new Metadata(
        'https://best-merchant.paysera.net', // referer (mandatory)
        'cms',           // platform
        '2.11.0',        // platform_version
        'my-awesome-plugin', // plugin_name
        '1.4.3'          // plugin_version
    ),
    new RedirectUrls(
        'https://best-merchant.paysera.net/success',
        'https://best-merchant.paysera.net/fail',
        'https://best-merchant.paysera.net/webhook',
        'https://best-merchant.paysera.net/cart' // optional "back to shop" URL
    ),
    PaymentOrderSource::CHECKOUT_PAGE // source (optional, must be a PaymentOrderSource value)
);
```

#### Payment order source

`source` describes where the order was initiated. Pass one of the values from
[`PaymentOrderSource`](../src/Entity/PaymentOrderSource.php):

- `PaymentOrderSource::CHECKOUT_PAGE` — the standard checkout page.
- `PaymentOrderSource::PRODUCT_PAGE_EXPRESS_CHECKOUT` — express checkout started from a product page.

The typed path rejects any other value with a `ValidationException`. The deprecated raw-array path
still accepts a free-form `source` string for backward compatibility.

### Raw-array construction (deprecated)

> ⚠️ **Deprecated since 2.2.0** — use the typed path above. The raw-array path does not validate the
> `source` allow-list, and any unrecognized `metadata.*` key is forwarded verbatim as custom metadata
> (so `referer` must be spelled correctly — a typo like `referrer` is silently sent as-is).

```php
<?php

use Paysera\CheckoutSdk\SdkFacade;
use Paysera\CheckoutSdk\Entity\PaymentLink\Experience;

...

/** @var SdkFacade $sdkFacade */
$paymentsFacade = $sdkFacade->getPaymentsFacade();

$paymentOrderRequest = $paymentsFacade->buildPaymentOrderCreateRequest(
    [
        'redirect_urls' => [
            'success_url' => 'https://best-merchant.paysera.net/success',
            'failure_url' => 'https://best-merchant.paysera.net/fail',
            'callback_url' => 'https://best-merchant.paysera.net/webhook',
            'cancel_url' => 'https://best-merchant.paysera.net/cart', // optional "back to shop" URL
        ],
        'metadata' => [
            'referer' => 'https://best-merchant.paysera.net', // mandatory
            'platform' => 'cms',
            'platform_version' => '2.11.0',
            'plugin_name' => 'my-awesome-plugin',
            'plugin_version' => '1.4.3',
        ],
        'source' => 'checkout_page', // optional; free-form on this path (not validated)
        'purchase' => [
            'reference' => 'CMS-ORDER-ID',
            'amount' => 14999, // in cents or minor units
            'currency' => 'EUR',
        ],
    ]
);

$paymentLinkRequest = $paymentsFacade->buildPaymentLinkCreateRequest(
    [
        'name' => 'Invoice #123',
        'lifetime' => 3600,
        'metadata' => [
            'referer' => 'https://best-merchant.paysera.net', // mandatory
        ],
        'experience' => [
            'language' => 'en',
            'payment_flow' => Experience::PAYMENT_FLOW_DIRECT,
        ],
        'payment_details' => [
            'key' => 'swedbank',
            'purpose' => 'Order #12345 - Leather Wallet',
            'country_code' => 'LT',
        ],
        'purchase' => [
            'amount' => 14999, // in cents or minor units
        ],
        'payer_information' => [
            'name' => 'John Doe',
            'email' => 'customer@example.paysera.test',
        ],
    ]
);

$paymentLinkResponse = $paymentsFacade->initiatePayment($paymentOrderRequest, $paymentLinkRequest);

...

$response->redirect($paymentLinkResponse->getPaymentUrl());
```

Method `initiatePayment()` works like:
    1. Create a payment order.
    2. Fill the payment link with created order ID and full amount.
    3. Creates payment link and returns a response with redirect URL.
You can split these steps and execute each of them manually with corresponding payments facade methods.
Method `initiatePayment()` returns a [response](../src/Entity/PaymentLinkCreateResponse.php) which contains `payment_url` of the Paysera checkout payment page or direct payment method.
Use this `payment_url` in your code and make a redirect in the chosen way or open new window with interactive flow.

## Manual Step-by-Step Payment Creation

Instead of `initiatePayment()`, you can execute each step separately:

```php
<?php

use Paysera\CheckoutSdk\SdkFacade;

/** @var SdkFacade $sdkFacade */
$paymentsFacade = $sdkFacade->getPaymentsFacade();

$orderRequest = $paymentsFacade->buildPaymentOrderCreateRequest([...]);
$orderResponse = $paymentsFacade->createPaymentOrder($orderRequest);

$linkRequest = $paymentsFacade->buildPaymentLinkCreateRequest([...]);
$linkRequest
    ->setOrderId($orderResponse->getOrderId())
    ->getPurchase()->setAmount($orderResponse->getPurchase()->getAmount())
;
$linkResponse = $paymentsFacade->createPaymentLink($linkRequest);
```

## Test payments

The response of `createPaymentOrder()` reports whether Paysera created the order as a test payment:

```php
<?php

$orderResponse = $paymentsFacade->createPaymentOrder($orderRequest);

if ($orderResponse->isTest()) {
    // Mark the store order as a test payment: no real money moves for it.
}
```

The flag is decided by the engine at the moment the order is created and describes **that order**, so it
is the authoritative source for labelling an order in the store. Reading the project's current test-mode
setting instead — from an eligibility response, cached or not — answers a different question and can
contradict the payment: the project may have been switched between the two calls. Orders created by an
engine that does not send the field read as live.

## Environment and Payment Statuses

```php
<?php

use Paysera\CheckoutSdk\SdkFacade;

/** @var SdkFacade $sdkFacade */
$paymentsFacade = $sdkFacade->getPaymentsFacade();

$environment = $paymentsFacade->getPaymentApiEnvironment();
$isSandbox = $environment->isSandbox();

$statuses = $paymentsFacade->getPaymentStatuses();

$isValid = $paymentsFacade->isPaymentStatusValid('completed');
```
