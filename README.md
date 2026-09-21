## Checkout integration SDK

[![Packagist Version](https://img.shields.io/packagist/v/paysera/lib-checkout-integration-sdk.svg)](https://packagist.org/packages/paysera/lib-checkout-integration-sdk)
[![Packagist Downloads](https://img.shields.io/packagist/dt/paysera/lib-checkout-integration-sdk.svg)](https://packagist.org/packages/paysera/lib-checkout-integration-sdk)
[![License: LGPL-3.0-or-later](https://img.shields.io/badge/License-LGPL%203.0--or--later-blue.svg)](LICENSE)

Paysera PHP SDK for integrating with the Paysera Checkout v3 API: payment initiation, callback verification, refunds, project eligibility, and localization — with a single `SdkFacade` entry point.

## Supported PHP versions

PHP 7.4, 8.0, 8.1, 8.2, 8.3, and 8.4. The minimum requirement is declared in `composer.json` as `^7.4 || ^8.0`.

## Requirements

- PHP 7.4 or higher
- `ext-curl`
- `ext-json`

## Installation

Install via Composer:

```bash
composer require paysera/lib-checkout-integration-sdk
```

## Obtaining sandbox credentials

A sandbox project with API credentials is required to run the SDK end to end. Self-service onboarding for external integrators is documented on the Paysera developer portal at <https://developers.paysera.com>.

## Code style

[PSR-12](https://www.php-fig.org/psr/psr-12). Built on top of [PSR](https://www.php-fig.org/psr/) standards (PSR-3 logging, PSR-6 cache, PSR-18 HTTP client, PSR-20 clock).

## Features

- **JWT Token Validation**: Automatic validation of JWT tokens with project ID extraction
- **PSR-Compliant Caching**: Configurable cache pool for improved performance
- **Payment Processing**: Full payment order creation and link generation
- **Callback Processing**: Secure webhook verification and processing
- **Project Eligibility**: Single-call eligibility check that combines payment-collection status and store-URL verification, with deterministic `failed_to_check` fallback for retry UX
- **Translations**: Namespace-based localization with SDK and plugin translation merging
- **Multiple Environments**: Support for both production and sandbox environments

## Quick Start

```php
<?php

use Paysera\CheckoutSdk\SdkFacadeBuilder;
use Paysera\CheckoutSdk\Entity\PaymentApiCredentials;

// Build SDK facade with minimal configuration
$sdkFacade = (new SdkFacadeBuilder())
    ->build()
;

// Authorize with your API credentials
$apiCredentials = new PaymentApiCredentials(
    'your-client-id',
    'your-client-secret'
);

$sdkFacade
    ->getAuthorizationFacade()
    ->authorize($apiCredentials)
;

// Now you're ready to process payments
```

## SDK Facade Configuration

The SDK facade can be customized with various options:

```php
<?php

use Paysera\CheckoutSdk\SdkFacadeBuilder;

$sdkFacade = (new SdkFacadeBuilder())
    // Optional: Set custom PSR-3 logger. NullLogger by default
    ->setLogger($logger)

    // Optional: Set custom PSR-18 HTTP client. CurlHttpClient by default
    ->setHttpClient($httpClient)

    // Optional: Set custom PSR-6 cache pool. InMemoryCache by default.
    // Caches Keycloak JWKS used for JWT signature verification.
    // Persistent pool (Redis/APCu/filesystem) is strongly recommended in
    // production — see "JWKS cache" section below.
    ->setCacheItemPool($cacheItemPool)

    // Optional: Set custom PSR-20 clock. System clock by default
    // Used for time-based operations like token expiration
    ->setClock($clock)

    // Optional: Set auth token repository. InMemoryRepository by default
    // Permanently stores payment API access tokens
    // Needed for using the same token between requests
    ->setPaymentApiAuthTokenRepository($paymentApiAuthTokenRepository)

    // Optional: Set credentials repository. InMemoryRepository by default
    // Permanently stores payment API credentials
    // Needed for automatically refreshing tokens
    ->setPaymentApiCredentialsRepository($paymentApiCredentialsRepository)

    // Optional: Set custom API client formatter. SecureApiClientFormatter by default
    // Controls how HTTP requests/responses are formatted in logs
    ->setApiClientFormatter($apiClientFormatter)

    ->build()
;
```

## JWKS cache

The SDK verifies JWT access token signatures against Keycloak's JWKS
endpoint (`/auth/realms/Paysera/protocol/openid-connect/certs`). The key
set is fetched once on cold start and cached for 30 days. The endpoint is
re-contacted only when an incoming JWT carries a `kid` that is not present
in the cached set (Keycloak key rotation).

To benefit from this strategy in production you **must** inject a
persistent PSR-6 cache pool via `setCacheItemPool()`. The default
`InMemoryCache` is scoped to a single PHP request and defeats the caching
completely in shared-nothing deployments (PHP-FPM, multi-container setups),
causing a `/certs` fetch on every validation.

Recommended backends: Redis, APCu, filesystem cache — anything that
survives across PHP request lifecycles. Examples:

- Symfony: `Symfony\Component\Cache\Adapter\RedisAdapter`
- Laravel: `Illuminate\Cache\Psr6\CachePool` wrapping the application cache
- WordPress/WooCommerce: any plugin bridging WP transients or object cache
  to PSR-6
- Plain PHP: `Symfony\Component\Cache\Adapter\FilesystemAdapter`

If `/certs` is unreachable during a rotation fallback, JWT validation
fails loudly — the SDK never accepts an unsigned or unverifiable token.

### Forcing a JWKS refresh

Normal Keycloak rotation is handled automatically via the unknown-`kid`
fallback. For incident response (revoked or compromised signing key),
the cached key set must be purged from the PSR-6 pool so the next JWT
validation cold-starts it from `/certs` again.

`Firebase\JWT\CachedKeySet` stores its entries under the `jwks` prefix,
but `setCacheKeys()` SHA-256-hashes the composite key whenever it exceeds
64 characters — and every real Paysera JWKS URL crosses that threshold.
The prefix is lost in the final storage key, which means a
`redis-cli --scan --pattern 'jwks*'` sweep will not match anything.

The reliable approach is to dedicate an isolated PSR-6 pool to the SDK
and clear it as a whole during an incident:

- Dedicated Redis DB: `redis-cli -n <sdk-db-index> FLUSHDB`
- Symfony Cache (dedicated pool): `bin/console cache:pool:clear <paysera-sdk-pool>`
- Dedicated filesystem cache: delete the cache directory
  (`rm -rf var/cache/paysera-sdk/*`) and reload the PHP-FPM pool
- APCu: `apcu_clear_cache()` only if APCu is not shared with unrelated
  application data; otherwise prefer a different backend

Sharing the SDK's PSR-6 pool with unrelated application caches defeats
this procedure — a full flush would also wipe business data. Keep the
pool SDK-scoped so incident response stays a one-liner.

### HTTP client timeouts

JWKS fetches go through the PSR-18 client injected via `setHttpClient()`
(or the default `CurlHttpClient` otherwise). During cold start and
rotation fallback, the call blocks the caller thread. A slow or
unreachable `/certs` endpoint with an unbounded client timeout turns
every such call into a hung checkout request.

**Required for production**: configure a finite connect timeout (~5 s)
and total-request timeout (~10 s) on the injected PSR-18 client.

- The default `CurlHttpClient` already sets `CURLOPT_CONNECTTIMEOUT=10`
  and `CURLOPT_TIMEOUT=30`.
- Symfony HttpClient: `HttpClient::create(['timeout' => 10, 'max_duration' => 10])`.
- Guzzle: `new Client(['connect_timeout' => 5, 'timeout' => 10])`.

## HTTP logging and redaction

Requests and responses are written to the PSR-3 logger passed to
`setLogger()` through `SecureApiClientFormatter`. Nothing is logged at all
while the default `NullLogger` is in place.

The formatter redacts sensitive query parameters, headers and body fields
before the line is written. Body redaction is **fail closed**: a body is
logged only when a body formatter claims its `Content-Type`. JSON
(`application/json` and any `+json` subtype, including the
`application/problem+json` of an RFC 7807 error) and
`application/x-www-form-urlencoded` are handled out of the box, matched on
the parsed media type — so parameters (`; charset=utf-8`), letter case and
a header sent twice make no difference. Anything else — an unknown media
type, or a response with no `Content-Type` header — is replaced with
`[UNSUPPORTED_CONTENT_TYPE]`, so an unrecognized payload never reaches the
log.

Redacted by default: the query parameters and body fields `client_id`,
`client_secret`, `access_token`, `password` and `token`, and the headers
`Authorization` and `Set-Cookie`. Extend either list through the
`SecureApiClientFormatter` constructor, and add handling for a further
content type with `addCustomBodyFormatter()` — a custom formatter is
consulted before the built-in ones, so it can also override how JSON or
form-urlencoded bodies are redacted.

## Exception context in logs

Internal SDK exceptions extend `BaseException` and carry diagnostic data that is
not part of the exception message: which field failed validation, what the API
actually answered. `getContextData(): array` returns that data as an array so a
PSR-3 logger can render it.

It does not reach a log on its own. A PSR-3 normalizer resolves the standard
`Throwable` fields — class, message, code, file, line, trace, previous — and
calls no other getters, so render the context explicitly in your own normalizer,
or pass it alongside the exception:

```php
use Paysera\CheckoutSdk\Exception\BaseException;
use Paysera\CheckoutSdk\Exception\IntegrationException;

try {
    $response = $sdkFacade->getPaymentsFacade()->createPaymentOrder($request);
} catch (IntegrationException $exception) {
    $previous = $exception->getPrevious();

    $logger->error($exception->getMessage(), [
        'exception' => $exception,
        'context' => $previous instanceof BaseException ? $previous->getContextData() : [],
    ]);
}
```

A public facade surfaces failures as `IntegrationException`, so the internal
exception that carries the context is the one behind `getPrevious()`.

`getContext(): string` is unchanged and still returns exactly what it returned
before.

Two things to know before passing the array straight to a handler.

Only an array keyed entirely by strings is merged into the top level. Everything
else — a list, an integer-keyed array, a scalar, a body that is not JSON — is
appended as a list under the reserved key `_raw`, so read it from there rather
than from the top level.

And the array holds the values as they were given, where `getContext()` flattened
everything to a string at write time. An object passed to `setContext()` is
therefore handed to your log formatter as an object, not as the flattened string
`getContext()` produced — a formatter that recurses into it can write more than
the string form ever did, and one that cannot encode it can fail on the whole
record.

## Documentation

### Use Cases
- [Authorization](docs/AUTHORIZE.md) - Authenticate with the Payment API and decode JWT tokens
- [Payment Initiation](docs/INITIATE_PAYMENT.md) - Create payment orders and generate payment links
- [Callback Processing](docs/PROCESS_PAYMENT_CALLBACK.md) - Securely process payment webhooks
- [Project Eligibility](docs/PROJECT_ELIGIBILITY.md) - Check whether the current store is allowed to collect payments
- [Merchant Area Deeplinks](docs/MERCHANT_AREA.md) - Build project-specific Merchant Area URLs from the stored token
- [Translations](docs/TRANSLATIONS.md) - Localization with SDK and plugin namespace support
- [Payment Method Logos](docs/PAYMENT_METHOD_ICONS.md) - Logo URLs provided by the API via `PaymentMethod::getLogoUrl()`
- [Local Development](docs/LOCAL_DEVELOPMENT.md) - Setup and testing in development environment

## Version History

See [CHANGELOG.md](CHANGELOG.md) for detailed version history and upgrade notes.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md). Paysera GitLab is the source of truth; the GitHub repository is a read-only mirror updated on every release tag.

## Security

Vulnerabilities should be reported privately — see [SECURITY.md](SECURITY.md). Please do not open public issues for security findings.

## License

LGPL-3.0-or-later. The full license text is in [LICENSE](LICENSE).
