## Environment Variables

The SDK uses these URLs by default:
- **Production:** `https://api.paysera.com`
- **Sandbox:** `https://sandbox.paysera.com`

For testing purposes, you can override URLs using environment variables:

| Variable | Default | Description |
|----------|---------|-------------|
| `PAYSERA_CHECKOUT_SDK_PAYMENT_API_PRODUCTION_BASE_URL` | `https://api.paysera.com` | Production API base URL |
| `PAYSERA_CHECKOUT_SDK_PAYMENT_API_SANDBOX_BASE_URL` | `https://sandbox.paysera.com` | Sandbox API base URL |

**Example:**
```bash
export PAYSERA_CHECKOUT_SDK_PAYMENT_API_PRODUCTION_BASE_URL=https://custom-api.example.com
export PAYSERA_CHECKOUT_SDK_PAYMENT_API_SANDBOX_BASE_URL=https://custom-sandbox.example.com
```

**Important:**
- Override will log a warning to help identify configuration
- Only use custom URLs in development/testing environments

## Build docker image
```
docker build -t lib-checkout-integration-sdk .
```
Default PHP version is 7.4, but you can change it by passing PHP_VERSION argument
```
docker build --build-arg PHP_VERSION=8.4 -t lib-checkout-integration-sdk .
```

## Run tests
```
docker run -it -u $UID -v $PWD:/app -w /app lib-checkout-integration-sdk composer phpunit
```

To run tests with custom URLs:
```
docker run -it -u $UID -v $PWD:/app -w /app \
  -e PAYSERA_CHECKOUT_SDK_PAYMENT_API_PRODUCTION_BASE_URL=https://custom-api.example.com \
  -e PAYSERA_CHECKOUT_SDK_PAYMENT_API_SANDBOX_BASE_URL=https://custom-sandbox.example.com \
  lib-checkout-integration-sdk composer phpunit
```

## Run tests coverage
```
docker run -it -u $UID -v $PWD:/app -w /app -e XDEBUG_MODE=coverage lib-checkout-integration-sdk composer test-coverage
```

## Analyse with PHPStan
```
docker run -it -u $UID -v $PWD:/app -w /app lib-checkout-integration-sdk composer analyse
```

## Ignore PHPStan errors
```
docker run -it -u $UID -v $PWD:/app -w /app lib-checkout-integration-sdk composer baseline
```

## Fix php-cs-fixer errors
```
docker run -it -u $UID -v $PWD:/app -w /app lib-checkout-integration-sdk composer format
```

## Debugging
WIP
