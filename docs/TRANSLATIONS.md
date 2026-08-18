## Translations

The SDK provides a dedicated Translations facade for fetching and using localized strings.
Translations are fetched from a remote translation proxy and support merging SDK base translations with plugin-specific overrides.

## Basic Usage

```php
<?php

use Paysera\CheckoutSdk\SdkFacade;

/**
 * @var SdkFacade $sdkFacade
 */
$translator = $sdkFacade
    ->getTranslationsFacade()
    ->getTranslator()
;

$label = $translator->translate('connect_button_label', 'en');
```

`getTranslator()` fetches SDK translations and returns a `Translator` instance.
`translate()` accepts a translation key, an optional locale (defaults to `en`), and an optional vocabulary name.

## Plugin Namespace

When building a CMS plugin, pass your plugin namespace to merge plugin-specific translations on top of SDK defaults:

```php
<?php

use Paysera\CheckoutSdk\SdkFacade;

/**
 * @var SdkFacade $sdkFacade
 */
$translator = $sdkFacade
    ->getTranslationsFacade()
    ->getTranslator('app_shopify_integration_v2')
;

$label = $translator->translate('custom_plugin_key', 'lt');
```

When a plugin namespace is provided:
1. SDK translations (`lib_checkout_integration_sdk`) are fetched first
2. Plugin translations are fetched and merged on top
3. Plugin keys override SDK keys for the same locale and vocabulary

The namespace must match the pattern `\w+` (alphanumeric and underscores only).

## Vocabulary Resolution

When no vocabulary is specified, the translator resolves keys in this order:
1. `messages.json` (preferred)
2. `messages.php` (fallback)

You can also specify a vocabulary explicitly:

```php
$value = $translator->translate('some_key', 'en', 'info.php');
```

## Buyer Consent

The buyer consent notice shown before payment is not just a string — it is a sentence, a link
URL and an anchor text that belong together. `getCustomerConsent()` returns all three, already
localized, so a plugin never has to know the translation keys or hardcode the rules URL:

```php
<?php

use Paysera\CheckoutSdk\SdkFacade;

/**
 * @var SdkFacade $sdkFacade
 */
$consent = $sdkFacade
    ->getTranslationsFacade()
    ->getCustomerConsent('lt', 'app_shopify_integration_v2')
;

printf(
    $consent->getText(),
    sprintf(
        '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
        $consent->getLinkUrl(),
        $consent->getLinkText()
    )
);
```

Both arguments are optional: the locale defaults to `en`, and the plugin namespace behaves
exactly as in `getTranslator()`.

`CustomerConsent::getText()` contains a single `%s` placeholder marking where the link belongs.
`getLinkUrl()` points at the payment initiation and account information service rules, localized
where a localized page exists and falling back to the English page where it does not.

Each field falls back to its English source value when the translations carry no value for it,
and the whole notice falls back when the translations request fails — see Error Handling below.
The defaults are exposed as `CustomerConsentProvider::DEFAULT_TEXT`, `::DEFAULT_LINK_URL` and
`::DEFAULT_LINK_TEXT`, and the keys as `::TEXT_KEY`, `::LINK_URL_KEY` and `::LINK_TEXT_KEY`, so a
plugin that needs to seed its own offline translation bundle can read them instead of copying
the strings.

## Error Handling

`getTranslator()` does not throw when the translation proxy request fails: it logs the failure
through the configured PSR-3 logger and returns an empty `Translator`, so `translate()` returns
`null` for every key. Callers are expected to supply their own default for a missing string.

```php
<?php

use Paysera\CheckoutSdk\SdkFacade;

/**
 * @var SdkFacade $sdkFacade
 */
$translator = $sdkFacade
    ->getTranslationsFacade()
    ->getTranslator()
;

$label = $translator->translate('connect_button_label', 'en') ?? 'Connect';
```

`getCustomerConsent()` applies that fallback for you, which is why it returns strings rather
than nullable ones.

## Migration from Infrastructure Facade

In versions before 0.6.0, translations were accessed through the Infrastructure facade:

```php
// Before (removed in 0.6.0)
$translator = $sdkFacade->getInfrastructureFacade()->getTranslator();

// After
$translator = $sdkFacade->getTranslationsFacade()->getTranslator();
```
