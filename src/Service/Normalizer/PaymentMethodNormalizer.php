<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer;

use Paysera\CheckoutSdk\Entity\Collection\PaymentCountryCollection;
use Paysera\CheckoutSdk\Entity\PaymentCountry;
use Paysera\CheckoutSdk\Entity\PaymentMethod;
use Paysera\CheckoutSdk\Entity\PaymentMethodCountryDisplay;
use Paysera\CheckoutSdk\Util\TypeConverter;

class PaymentMethodNormalizer
{
    private const ALLOWED_LOGO_URL_SCHEMES = ['http', 'https'];

    private TypeConverter $typeConverter;

    public function __construct(TypeConverter $typeConverter)
    {
        $this->typeConverter = $typeConverter;
    }

    public function denormalize(array $rawData): PaymentMethod
    {
        return new PaymentMethod(
            $this->getProviderProperty('key', $rawData),
            $this->getProviderProperty('title', $rawData),
            $this->getProviderProperty('description', $rawData),
            $this->getProviderProperty('type', $rawData),
            $this->getProviderProperty('flow', $rawData),
            $this->normalizeCountries($rawData['available_countries'] ?? []),
            $this->resolveLogoUrl($rawData, 'logo_url'),
            $this->resolveLogoUrl($rawData, 'logo_wide_url'),
            $this->normalizeCountryDisplays($rawData['country_display'] ?? [])
        );
    }

    protected function getProviderProperty(
        string $propertyName,
        array $rawData
    ): string {
        if (isset($rawData[$propertyName]) === false) {
            return '';
        }

        return $this->typeConverter->convert($rawData[$propertyName], TypeConverter::STRING);
    }

    private function normalizeCountries(array $countries): PaymentCountryCollection
    {
        $collection = new PaymentCountryCollection();

        foreach ($countries as $country) {
            $collection->append(
                new PaymentCountry($this->typeConverter->convert($country, TypeConverter::STRING))
            );
        }

        return $collection;
    }

    /**
     * @param mixed $countryDisplays
     *
     * @return PaymentMethodCountryDisplay[]
     */
    private function normalizeCountryDisplays($countryDisplays): array
    {
        if (!is_array($countryDisplays)) {
            return [];
        }

        $normalized = [];

        foreach ($countryDisplays as $countryCode => $countryDisplay) {
            if (!is_array($countryDisplay)) {
                continue;
            }

            $normalized[] = new PaymentMethodCountryDisplay(
                (string) $countryCode,
                $this->getNullableText('title', $countryDisplay),
                $this->getNullableLogoUrl('logo_url', $countryDisplay),
                $this->getNullableLogoUrl('logo_wide_url', $countryDisplay)
            );
        }

        return $normalized;
    }

    private function getNullableText(string $propertyName, array $rawData): ?string
    {
        if (!isset($rawData[$propertyName]) || !is_scalar($rawData[$propertyName])) {
            return null;
        }

        $value = trim($this->typeConverter->convert($rawData[$propertyName], TypeConverter::STRING));

        return $value !== '' ? $value : null;
    }

    private function getNullableLogoUrl(string $propertyName, array $rawData): ?string
    {
        if (!isset($rawData[$propertyName]) || !is_string($rawData[$propertyName])) {
            return null;
        }

        $logoUrl = $this->resolveLogoUrl($rawData, $propertyName);

        return $logoUrl !== '' ? $logoUrl : null;
    }

    private function resolveLogoUrl(array $rawData, string $propertyName): string
    {
        $apiLogoUrl = $this->getProviderProperty($propertyName, $rawData);

        if ($apiLogoUrl !== '' && $this->isSafeHttpUrl($apiLogoUrl)) {
            return $apiLogoUrl;
        }

        return '';
    }

    private function isSafeHttpUrl(string $url): bool
    {
        if (preg_match('/[\x00-\x1F\x7F]/', $url) === 1) {
            return false;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return is_string($scheme) && in_array(strtolower($scheme), self::ALLOWED_LOGO_URL_SCHEMES, true);
    }
}
