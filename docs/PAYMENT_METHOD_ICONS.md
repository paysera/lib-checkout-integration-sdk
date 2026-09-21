# Payment Method Logos

The Paysera Checkout API returns a logo URL for each payment method. The SDK
exposes it via `PaymentMethod::getLogoUrl()`. The SDK no longer bundles SVG
icons — logos are served centrally by the backend, so integrations render the
URL directly instead of maintaining per-plugin copies.

## How it works

1. The API response carries a `logo_url` per payment method.
2. `PaymentMethodNormalizer` populates `PaymentMethod::getLogoUrl()` from that
   field. The value is validated as a safe `http`/`https` URL; anything else
   (unsafe scheme, malformed URL, CRLF) yields an empty string.
3. If the API does not provide a logo URL, `getLogoUrl()` returns `''` —
   integrations should render their own placeholder.

## Usage

```php
$logoUrl = $paymentMethod->getLogoUrl();
if ($logoUrl !== '') {
    // e.g. render <img src="$logoUrl" alt="...">
}
```

`PaymentMethod::getIconUrl()` is **deprecated** and simply delegates to
`getLogoUrl()`. Use `getLogoUrl()` in new code.
