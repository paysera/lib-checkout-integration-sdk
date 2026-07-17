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

## Error Handling

`getTranslator()` throws `IntegrationException` if the translation proxy request fails.

```php
<?php

use Paysera\CheckoutSdk\SdkFacade;
use Paysera\CheckoutSdk\Exception\IntegrationException;

/**
 * @var SdkFacade $sdkFacade
 */
try {
    $translator = $sdkFacade
        ->getTranslationsFacade()
        ->getTranslator()
    ;
} catch (IntegrationException $exception) {
    // Handle translation fetch failure
}
```

## Migration from Infrastructure Facade

In versions before 0.6.0, translations were accessed through the Infrastructure facade:

```php
// Before (removed in 0.6.0)
$translator = $sdkFacade->getInfrastructureFacade()->getTranslator();

// After
$translator = $sdkFacade->getTranslationsFacade()->getTranslator();
```
