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

// https://bank.paysera.com/shell/checkout/{project_id}/overview
$rootUrl = $merchantArea->getRootUrl();

// https://bank.paysera.com/shell/checkout/{project_id}/integrations
$credentialsUrl = $merchantArea->getCredentialsUrl();

// https://bank.paysera.com/shell/checkout/{project_id}/settings/websites
$websitesValidationUrl = $merchantArea->getWebsitesValidationUrl();
```

All methods return `null` when the project id cannot be resolved — for example
when no authorization token is stored yet, or the stored token cannot be
decoded. Callers should treat `null` as "deeplink not available" and never block
the page on it.

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
