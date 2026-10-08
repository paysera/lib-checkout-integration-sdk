<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Provider;

use Paysera\CheckoutSdk\Entity\CustomerConsent;
use Paysera\CheckoutSdk\Service\Translator;

class CustomerConsentProvider
{
    public const TEXT_KEY = 'payment_methods_customer_consent';
    public const LINK_URL_KEY = 'payment_methods_customer_consent_link';
    public const LINK_TEXT_KEY = 'payment_methods_customer_consent_link_content';

    public const DEFAULT_TEXT = 'Please be informed that the account information and payment initiation services'
        . ' will be provided to you by Paysera in accordance with these %s. By proceeding with this payment,'
        . ' you agree to receive this service and the service terms and conditions.';
    public const DEFAULT_LINK_URL =
        'https://www.paysera.com/v2/en/legal/rules-for-the-provision-of-the-payment-initiation-service';
    public const DEFAULT_LINK_TEXT = 'rules';

    public function getConsent(Translator $translator, ?string $locale = null): CustomerConsent
    {
        return new CustomerConsent(
            $this->resolve($translator, self::TEXT_KEY, $locale, self::DEFAULT_TEXT),
            $this->resolve($translator, self::LINK_URL_KEY, $locale, self::DEFAULT_LINK_URL),
            $this->resolve($translator, self::LINK_TEXT_KEY, $locale, self::DEFAULT_LINK_TEXT)
        );
    }

    private function resolve(Translator $translator, string $key, ?string $locale, string $default): string
    {
        $value = $translator->translate($key, $locale);

        return $value === null || $value === '' ? $default : $value;
    }
}
