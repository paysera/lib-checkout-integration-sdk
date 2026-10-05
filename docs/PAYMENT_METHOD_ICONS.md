# Payment Method Logos and Display

The Paysera Checkout API returns logos and titles for each payment method. The
SDK no longer bundles SVG icons — logos are served centrally by the backend, so
integrations render the URLs directly instead of maintaining per-plugin copies.

## What the API returns

| Field | SDK getter | Meaning |
|---|---|---|
| `logo_url` | `PaymentMethod::getLogoUrl()` | Round icon (48 px disc), meant to be shown next to the title |
| `logo_wide_url` | `PaymentMethod::getLogoWideUrl()` | Rectangular wordmark that identifies the method on its own |
| `title` | `PaymentMethod::getTitle()` | Catalogue title |
| `country_display` | `PaymentMethod::getCountryDisplays()` / `getCountryDisplay($countryCode)` | Name and logos overridden for a payer country |

Every URL is validated as a safe `http`/`https` URL; anything else (unsafe
scheme, malformed URL, CRLF) yields `''` (or `null` inside a country entry). A
missing logo is `''` — integrations should render their own placeholder.

Appending `/wide` to `logo_url` by hand is no longer needed: use
`getLogoWideUrl()` or the `full_logo` display mode below.

## Display mode

`Payments::getPaymentMethods()` accepts optional display options:

```php
use Paysera\CheckoutSdk\Entity\PaymentMethodDisplayMode;
use Paysera\CheckoutSdk\Entity\PaymentMethodDisplayOptions;

$options = new PaymentMethodDisplayOptions(
    PaymentMethodDisplayMode::FULL_LOGO, // or LOGO_AND_TITLE, or null
    false                                // per-country logos, off by default
);

$methods = $sdkFacade->getPaymentsFacade()->getPaymentMethods($filter, $options);

foreach ($methods as $method) {
    $display = $method->getDisplay($buyerCountryCode);
    // $display->getLogoUrl(), $display->getTitle(), $display->shouldShowTitle()
}
```

An unknown mode throws `ValidationException` when the options are created.

### `full_logo`

Each method returns its rectangular logo, and the title is not needed:

```php
$options = new PaymentMethodDisplayOptions(PaymentMethodDisplayMode::FULL_LOGO);
$display = $method->withDisplayOptions($options)->getDisplay('LT');

$display->getLogoUrl();      // logo_wide_url
$display->shouldShowTitle(); // false
```

A method without a rectangular logo falls back to the round logo with the
title (`shouldShowTitle()` is `true`) — nothing fails.

### `logo_and_title`

Each method returns its round logo and its title:

```php
$options = new PaymentMethodDisplayOptions(PaymentMethodDisplayMode::LOGO_AND_TITLE);
$display = $method->withDisplayOptions($options)->getDisplay('LT');

$display->getLogoUrl();      // logo_url
$display->getTitle();        // title for the buyer's country
$display->shouldShowTitle(); // true
```

### No options

`getPaymentMethods()` without display options returns the methods exactly as
before, and `getDisplay()` resolves to the round logo with the title.

## Payer country

`country_display` holds only the countries that override something, and a
field that is not overridden is `null`. The full map is returned whatever
country the buyer is in.

### Name

`getTitleForCountry($countryCode)` — and `getDisplay($countryCode)->getTitle()`
— return the country's own name when the entry exists and its title is not
empty, otherwise the catalogue title. An unknown or empty country code, a
missing entry and a `null` title all fall back. The country code is
case-insensitive.

```php
$method->getTitleForCountry('LT'); // "Inbank lizingas"
$method->getTitleForCountry('DE'); // "Hire Purchase"
$method->getTitleForCountry(null); // "Hire Purchase"
```

`getTitle()` always returns the catalogue title.

### Logo

Per-country logos follow the same resolution but are used only when switched on
with the second argument of `PaymentMethodDisplayOptions`. The switch covers all
methods and countries. With it off, every method shows its catalogue logo.

```php
$options = new PaymentMethodDisplayOptions(PaymentMethodDisplayMode::FULL_LOGO, true);
$method->withDisplayOptions($options)->getDisplay('LT')->getLogoUrl();
// country_display.LT.logo_wide_url, or logo_wide_url when the country has none
```

In `logo_and_title` mode the country's `logo_url` is used, which is `null` for
every country the API currently overrides, so the round catalogue logo is shown.

`PaymentMethod::withDisplayOptions()` returns a copy; the original method is not
changed.

## Deprecated

`PaymentMethod::getIconUrl()` is **deprecated** and simply delegates to
`getLogoUrl()`. Use `getLogoUrl()` in new code.
