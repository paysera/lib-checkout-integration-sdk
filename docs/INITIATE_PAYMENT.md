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

### Payer-set amount

An order can leave the amount to the payer, who enters it on the payment page. Pass `null` as the
amount and `true` as `payerSetsAmount`, optionally with the limits the payer's amount must stay
within. All amounts are in minor units of the order currency, and the currency stays mandatory.

```php
<?php

use Paysera\CheckoutSdk\Entity\Metadata;
use Paysera\CheckoutSdk\Entity\PaymentOrder\Purchase;

$paymentOrderRequest = $paymentsFacade->buildPaymentOrderCreateRequestFromValues(
    new Purchase(
        'DONATION-2026-09',
        null,  // amount: entered by the payer
        'EUR',
        true,  // payerSetsAmount
        500,   // minimumAmount (optional; the API applies 1, i.e. 0.01, when omitted)
        50000  // maximumAmount (optional; the API applies its platform maximum when omitted)
    ),
    new Metadata('https://best-merchant.paysera.net')
);

$paymentLinkRequest = $paymentsFacade->buildPaymentLinkCreateRequest(
    [
        'name' => 'Donation',
        'metadata' => [
            'referer' => 'https://best-merchant.paysera.net',
        ],
        'experience' => [
            'language' => 'en',
        ],
        // No purchase.amount: the link takes the amount rules from its order.
    ]
);

$paymentLinkResponse = $paymentsFacade->initiatePayment($paymentOrderRequest, $paymentLinkRequest);
```

The SDK checks the structural rules before sending the order. A violation stops the call:
`createPaymentOrder()` / `initiatePayment()` throw an `IntegrationException` with code
`BaseException::E_VALIDATION`, whose previous exception is a `ValidationException`. Its
`getContextData()` maps each field to a message ending with the API's error code:

| rule | field | code |
|---|---|---|
| no `amount` while `payerSetsAmount` is true | `purchase.amount` | `payer_set_amount_with_amount` |
| limits only together with `payerSetsAmount` | `purchase.minimum_amount`, `purchase.maximum_amount` | `payer_set_amount_limits_without_flag` |
| when both limits are sent, the minimum is below the maximum | `purchase.minimum_amount` | `minimum_amount_not_below_maximum` |

Without `payerSetsAmount` the amount is required exactly as before. The floor and the platform
maximum are platform policy that can change, so the API alone checks them: a minimum below 1
(`minimum_amount_below_floor`), a maximum above the platform maximum
(`maximum_amount_above_platform_maximum`), and a single limit on the wrong side of the other bound's
default (`minimum_amount_not_below_maximum`). Two more rules depend on the project and are checked by
the API only: the project must have the payer-set amount feature enabled
(`payer_set_amount_not_enabled`), and the project must not have split payments enabled (automatic
splits or payer tips) and the order must not carry splits (`payer_set_amount_with_split_payments`).
Those failures come back from `createPaymentOrder()` / `initiatePayment()` as an `IntegrationException`.

For a payer-set order, `getPurchase()->getAmount()` on the order response returns `null`,
`isPayerSetsAmount()` returns `true`, and `getMinimumAmount()` / `getMaximumAmount()` return the limits
as you sent them: `null` for an omitted limit, in which case the API applies 1 and its platform maximum.
The link create response carries the same three values, which the link takes from its order:
`getPurchase()->isPayerSetsAmount()`, `getMinimumAmount()` and `getMaximumAmount()` on
`PaymentLinkCreateResponse`. `initiatePayment()` copies the order's `null` amount to the link. When you create the link yourself, leave its amount empty: the API rejects a link to a
payer-set order that carries an amount with `payer_set_amount_with_amount`.

### Suggested amount

A payer-set order can prefill the amount field with a suggested amount. The payer can still change
it within the limits. Pass it as the seventh `Purchase` argument, in minor units:

```php
<?php

use Paysera\CheckoutSdk\Entity\Metadata;
use Paysera\CheckoutSdk\Entity\PaymentOrder\Purchase;

$paymentOrderRequest = $paymentsFacade->buildPaymentOrderCreateRequestFromValues(
    new Purchase(
        'DONATION-2026-10',
        null,  // amount: entered by the payer
        'EUR',
        true,  // payerSetsAmount
        500,   // minimumAmount
        50000, // maximumAmount
        2500   // suggestedAmount (optional): prefilled 25.00
    ),
    new Metadata('https://best-merchant.paysera.net')
);
```

A payment link can override the order's suggested amount, for example to send different donors
different suggestions for the same order. Leave the link amount empty and set only the override
(`PaymentLink\Purchase::setSuggestedAmount()` does the same on a built request):

```php
<?php

$paymentLinkRequest = $paymentsFacade->buildPaymentLinkCreateRequest(
    [
        'name' => 'Donation',
        'metadata' => [
            'referer' => 'https://best-merchant.paysera.net',
        ],
        'experience' => [
            'language' => 'en',
        ],
        'purchase' => [
            'suggested_amount' => 5000, // overrides the order's suggested amount for this link
        ],
    ]
);
```

The rules the SDK checks before the call:

| rule | field | code |
|---|---|---|
| a suggested amount only together with `payerSetsAmount` (on a link: not together with a link `amount`) | `purchase.suggested_amount` | `suggested_amount_without_payer_set` |
| on an order, the minimum ≤ suggested amount ≤ the maximum, for the limits you send | `purchase.suggested_amount` | `suggested_amount_out_of_limits` |

The API checks the same range against the limits it applies, so a limit you omit is checked there:
1 for the minimum and its platform maximum for the maximum. On a link the SDK does not check the
range at all, because the link does not carry the order's limits; the API checks the override
against the limits the link inherits from its order and rejects it with the same codes. The order create response returns the order's suggested amount
through `getPurchase()->getSuggestedAmount()`, `null` when it has none.

### Amount buttons

A payer-set order can show the payer three or four preset amounts to pick from instead of typing
one. Pass them as the eighth `Purchase` argument, in minor units and ascending order:

```php
<?php

use Paysera\CheckoutSdk\Entity\Metadata;
use Paysera\CheckoutSdk\Entity\PaymentOrder\Purchase;

$paymentOrderRequest = $paymentsFacade->buildPaymentOrderCreateRequestFromValues(
    new Purchase(
        'DONATION-2026-10',
        null,               // amount: entered by the payer
        'EUR',
        true,               // payerSetsAmount
        500,                // minimumAmount
        50000,              // maximumAmount
        null,               // suggestedAmount
        [1000, 2500, 5000]  // amountButtons (optional): 10.00, 25.00, 50.00
    ),
    new Metadata('https://best-merchant.paysera.net')
);
```

A payment link can replace the order's buttons with its own. Leave the link amount empty and set
only the override (`PaymentLink\Purchase::setAmountButtons()` does the same on a built request);
without it, or with an empty list, the link keeps the order's buttons:

```php
<?php

$paymentLinkRequest = $paymentsFacade->buildPaymentLinkCreateRequest(
    [
        'name' => 'Donation',
        'metadata' => [
            'referer' => 'https://best-merchant.paysera.net',
        ],
        'experience' => [
            'language' => 'en',
        ],
        'purchase' => [
            'amount_buttons' => [500, 1500, 3000, 6000], // replaces the order's amount buttons for this link
        ],
    ]
);
```

The rules the SDK checks before the call, in the API's order:

| rule | field | code |
|---|---|---|
| amount buttons only together with `payerSetsAmount` (on a link: not together with a link `amount`) | `purchase.amount_buttons` | `amount_buttons_without_payer_set` |
| three or four amounts | `purchase.amount_buttons` | `amount_buttons_count` |
| each amount greater than the one before it, so no duplicates | `purchase.amount_buttons` | `amount_buttons_not_ascending` |
| on an order, the minimum ≤ each amount ≤ the maximum, for the limits you send | `purchase.amount_buttons` | `amount_buttons_out_of_limits` |

Each amount must be an `int`: a float or a numeric string is rejected locally rather than rounded,
so the payer never sees an amount you did not send. A list with gaps in its keys, such as the
result of `array_filter()`, is read as a plain list. The amounts are stored as sent; the SDK does
not sort them. A limit you omit is checked by the API, which applies 1 and its platform maximum. On
a link the SDK does not check the range, because the link does not carry the order's limits; the
API checks the override against the limits the link inherits from its order. The order and link create
responses return the buttons through `getPurchase()->getAmountButtons()`, an empty list when there
are none.

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
```

### `purchase.reference` character set

The reference ends up in the bank payment purpose, so the API restricts it to the SWIFT "X"
character set plus underscore: `A-Za-z0-9`, space, and `_ / ? : ( ) . , ' + -`. The SDK now
enforces the same rule locally, so a violation raises a `ValidationException` before the request
is sent instead of coming back as an HTTP 400.

| value | accepted |
|---|---|
| `CMS-ORDER-ID` | yes |
| `12345 - Anne-Marie` | yes |
| `12345 - O'Brien` | yes |
| `Order #12345` | no — `#` is outside the set |
| `12345 - Jokūbas` | no — diacritics are outside the set |
| `12345 - Ruze & Co` | no — `&` is outside the set |

Customer-facing text belongs in `payment_details.purpose`, which is capped at 255 characters but
not restricted this way — see the payment link example below.

```php

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
    2. Fill the payment link with created order ID and full amount (none for a payer-set order).
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
