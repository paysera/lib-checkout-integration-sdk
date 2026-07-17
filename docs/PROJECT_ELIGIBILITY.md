## Project Eligibility

Tells the merchant plugin whether the current Paysera project is allowed to collect payments from the current store URL. The SDK calls two backend endpoints (`getProjectInfoById`, `getUrlsByProjectId`), applies the eligibility business rules, and returns a single typed response so plugins do not have to duplicate this logic across WordPress, Magento, PrestaShop, OpenCart, and Shopify integrations.

The eligibility decision combines two checks:

- **Payment collection enabled** — the project's `payment_collection_status` is `enabled` (`disabled` counts as not enabled).
- **URL verified** — the merchant's current store URL is registered against the project and marked as `verified` on the backend.

Only when **both** flags are `true` does the SDK return `eligible`. Any other combination returns `ineligible`.

**Test mode bypass.** When `payment_collection_status` is `test_mode`, the SDK returns `eligible` unconditionally — neither payment collection nor URL verification is required — so the merchant can connect and create test payments before URL verification, review and activation are complete. On this path the response's `testMode` flag is `true` (elsewhere `false`), so plugins can surface a test-mode indication; live payment collection remains impossible server-side regardless. See the response shapes below.

If either backend call fails after one automatic retry, the SDK returns `failed_to_check` instead of throwing — plugins can render a deterministic Retry UX without wrapping the call in try/catch.

## Basic usage

```php
<?php

use Paysera\CheckoutSdk\Entity\PaymentApiCredentials;
use Paysera\CheckoutSdk\Entity\ProjectEligibilityCheckRequest;
use Paysera\CheckoutSdk\SdkFacade;
use Paysera\CheckoutSdk\SdkFacadeBuilder;

/**
 * @var SdkFacade $sdkFacade
 */
$sdkFacade = (new SdkFacadeBuilder())
    ->setPaymentApiAuthTokenRepository($paymentApiAuthTokenRepository)
    ->setPaymentApiCredentialsRepository($paymentApiCredentialsRepository)
    ->build()
;

$sdkFacade
    ->getAuthorizationFacade()
    ->authorize(new PaymentApiCredentials($clientId, $clientSecret))
;

$request = new ProjectEligibilityCheckRequest('https://shop.example.test');

$response = $sdkFacade
    ->getProjectEligibilityFacade()
    ->checkProjectEligibility($request)
;

switch ($response->getStatus()->getValue()) {
    case 'eligible':
        // Show the Paysera payment option in checkout.
        // $response->isTestMode() === true marks a test-mode project (test payments only).
        break;
    case 'ineligible':
        // Hide the option and surface a hint based on the flags.
        $paymentEnabled = $response->getPaymentCollectionEnabled(); // bool
        $urlVerified = $response->getUrlVerified();                 // bool
        break;
    case 'failed_to_check':
        // Could not determine eligibility — show a Retry control.
        break;
}
```

The current store URL is passed to the SDK as the `storeUrl` argument on `ProjectEligibilityCheckRequest`. The SDK forwards it to the backend's `referrer` query parameter; the project ID is extracted from the JWT, so plugins never pass it explicitly.

## Response shapes

`checkProjectEligibility()` always returns a `ProjectEligibilityResponse` with four fields: `status`, `paymentCollectionEnabled`, `urlVerified`, `testMode`. The combinations below cover every possible outcome.

**Eligible — both checks pass:**

```php
[
    'status' => 'eligible',
    'payment_collection_enabled' => true,
    'url_verified' => true,
    'test_mode' => false,
]
```

**Eligible — test mode (bypasses both checks):**

```php
[
    'status' => 'eligible',
    'payment_collection_enabled' => true,
    'url_verified' => false, // real value; not required in test mode
    'test_mode' => true,
]
```

**Ineligible — payment collection disabled:**

```php
[
    'status' => 'ineligible',
    'payment_collection_enabled' => false,
    'url_verified' => true,
    'test_mode' => false,
]
```

**Ineligible — store URL not verified:**

```php
[
    'status' => 'ineligible',
    'payment_collection_enabled' => true,
    'url_verified' => false,
    'test_mode' => false,
]
```

**Ineligible — both checks fail:**

```php
[
    'status' => 'ineligible',
    'payment_collection_enabled' => false,
    'url_verified' => false,
    'test_mode' => false,
]
```

**Failed to check — eligibility could not be determined:**

```php
[
    'status' => 'failed_to_check',
    'payment_collection_enabled' => null,
    'url_verified' => null,
    'test_mode' => null,
]
```

All three flags are `null` (rather than `false`) on `failed_to_check` because the SDK cannot distinguish a disabled-on-purpose (or non-test-mode) project from one whose status it never observed. Plugins should treat `null` as "unknown" and prompt the merchant to retry.

## Retry and failure contract

For each backend call, the SDK performs one automatic retry after a 1-second delay on transient failures (HTTP `5xx`, `408 Request Timeout`, `429 Too Many Requests`, and transport-level errors). Token-expiry (`401`) is handled separately by the existing token-refresh loop. Authoritative client errors (`400`, `403`, `404`, `422`, etc.) are not retried — they are deterministic and would not change on a second attempt.

If a call still fails after the retry, the SDK swallows the exception, logs it with full context, and returns the `failed_to_check` response shown above. The previous failure state is not cached: each call to `checkProjectEligibility()` issues fresh HTTP requests, so a second invocation after `failed_to_check` (e.g. from a Retry button) will re-attempt and produce the actual current eligibility.

`checkProjectEligibility()` itself never throws. The closest wrapper that does throw is `PaymentApiClient` directly, which is not part of the public eligibility API.
