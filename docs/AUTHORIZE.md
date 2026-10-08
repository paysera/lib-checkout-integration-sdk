## API Authorization
The payment API access requires the API token, which is unique for provided credentials.
The SDK automatically validates JWT tokens and extracts project information from them.

## Basic usage

```php
<?php

use Paysera\CheckoutSdk\SdkFacade;
use Paysera\CheckoutSdk\SdkFacadeBuilder;
use Paysera\CheckoutSdk\Entity\PaymentApiCredentials;

...

$sdkFacade = (new SdkFacadeBuilder())
    ->setPaymentApiAuthTokenRepository($paymentApiAuthTokenRepository)
    ->setPaymentApiCredentialsRepository($paymentApiCredentialsRepository)
    ->build()
;

...

$apiCredentials = new PaymentApiCredentials(
    (string) $clientId,
    (string) $clientSecret
);

/**
 * @var SdkFacade $sdkFacade
 */
$authToken = $sdkFacade
    ->getAuthorizationFacade()
    ->authorize($apiCredentials)
;

...

// Get all available payment methods
$allPaymentMethods = $sdkFacade
    ->getPaymentsFacade()
    ->getPaymentMethods()
;

// Get payment methods filtered by transaction amount and currency
// Amount is in cents (e.g., 1500 = 15.00 EUR)
// Only payment methods whose limits encompass the specified amount will be returned
use Paysera\CheckoutSdk\Entity\PaymentMethodFilter;

$filter = new PaymentMethodFilter(1500, 'EUR');
$filteredPaymentMethods = $sdkFacade
    ->getPaymentsFacade()
    ->getPaymentMethods($filter)
;

```

Method `authorize()` returns fresh auth token and automatically stores this token with credentials if repositories were provided.
Usage of `setPaymentApiAuthTokenRepository()` is optional.
If not set, `InMemoryPaymentApiAuthTokenRepository` will be used by default, but in this case the token will not be permanently saved and will be lost after the script execution.
Usage of `setPaymentApiCredentialsRepository()` is optional.
If not set, `InMemoryPaymentApiCredentialsRepository` will be used by default, but in this case the credentials will not be permanently saved and will be lost after the script execution.

## Retrieving Decoded Token

The SDK can decode the stored JWT token to extract project ID, client ID, and expiration info:

```php
<?php

use Paysera\CheckoutSdk\SdkFacade;

/**
 * @var SdkFacade $sdkFacade
 */
$decodedToken = $sdkFacade
    ->getAuthorizationFacade()
    ->getDecodedToken()
;

if ($decodedToken !== null) {
    $projectId = $decodedToken->getProjectId();
    $clientId = $decodedToken->getClientId();
    $expiresAt = $decodedToken->getExpiresAt();
}
```

Returns `null` if no token is stored. Throws `IntegrationException` if the stored token cannot be decoded.

## Re-authorization

If the token has expired, you can refresh it using stored credentials:

```php
<?php

use Paysera\CheckoutSdk\SdkFacade;

/**
 * @var SdkFacade $sdkFacade
 */
$freshToken = $sdkFacade
    ->getAuthorizationFacade()
    ->reauthorize()
;
```

This uses the credentials stored during the initial `authorize()` call. Requires `setPaymentApiCredentialsRepository()` to be configured with a persistent repository.

## Retrieving Stored Credentials and Token

You can check if credentials or a token are already stored:

```php
<?php

use Paysera\CheckoutSdk\SdkFacade;

/**
 * @var SdkFacade $sdkFacade
 */
$credentials = $sdkFacade
    ->getAuthorizationFacade()
    ->getStoredPaymentApiCredentials()
;

$authToken = $sdkFacade
    ->getAuthorizationFacade()
    ->getStoredPaymentApiAuthToken()
;
```

Both methods return `null` if nothing is stored.
