## Merchant Area deeplinks

The SDK builds project-specific Merchant Area deeplink URLs so integrations can
link the merchant straight to their project in the Paysera Merchant Area without
constructing URLs themselves.

The project id is read from the stored authorization token, and the base host is
selected from the configured environment (production / sandbox). Integrations
only consume the ready URL.

## Basic usage

```php
<?php

use Paysera\CheckoutSdk\SdkFacade;
use Paysera\CheckoutSdk\SdkFacadeBuilder;

$sdkFacade = (new SdkFacadeBuilder())
    ->setPaymentApiAuthTokenRepository($paymentApiAuthTokenRepository)
    ->setPaymentApiCredentialsRepository($paymentApiCredentialsRepository)
    ->build()
;

/**
 * @var SdkFacade $sdkFacade
 */
$merchantArea = $sdkFacade->getMerchantAreaFacade();

// https://bank.paysera.com/shell/checkout
$projectListUrl = $merchantArea->getProjectListUrl();

// https://bank.paysera.com/shell/checkout/{project_id}/overview
$rootUrl = $merchantArea->getRootUrl();

// https://bank.paysera.com/shell/checkout/{project_id}/integrations
$credentialsUrl = $merchantArea->getCredentialsUrl();

// https://bank.paysera.com/shell/checkout/{project_id}/settings/websites
$websitesValidationUrl = $merchantArea->getWebsitesValidationUrl();
```

`getRootUrl()`, `getCredentialsUrl()` and `getWebsitesValidationUrl()` return
`null` when the project id cannot be resolved — for example when no
authorization token is stored yet, or the stored token cannot be decoded.
Callers should treat `null` as "deeplink not available" and never block the page
on it. `getProjectListUrl()` always returns a URL, because it needs no project
id.

## Routes

| Method | Merchant Area page |
|---|---|
| `getProjectListUrl()` | Project list |
| `getRootUrl()` | Project overview — review submission, correction message, and the fallback for identity verification and the service agreement |
| `getCredentialsUrl()` | Integrations (API credentials) |
| `getWebsitesValidationUrl()` | Settings > Websites (store URL confirmation) |

## Before authorization

The project id comes from the authorization token, so before the merchant has
authorized the only available link is the project list. From there the merchant
opens their project, then goes to Integrations > API credentials. See
[Post-authorization banner](PROJECT_ELIGIBILITY.md#post-authorization-banner)
for the copy to show next to it.

## Environment

The base host is chosen from the configured `PaymentApiEnvironment`:

| Environment | Base host |
|---|---|
| production (default) | `https://bank.paysera.com` |
| sandbox | `https://sandbox.bank.paysera.com` |

Both hosts can be overridden with environment variables (useful for local
development and integration testing):

```bash
export PAYSERA_CHECKOUT_SDK_MERCHANT_AREA_PRODUCTION_BASE_URL=https://custom.example.com
export PAYSERA_CHECKOUT_SDK_MERCHANT_AREA_SANDBOX_BASE_URL=https://custom-sandbox.example.com
```
