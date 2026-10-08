<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Provider;

use Paysera\CheckoutSdk\Entity\Collection\PaymentCountryCollection;
use Paysera\CheckoutSdk\Entity\PaymentCountry;
use Paysera\CheckoutSdk\Exception\InvalidTypeException;

class PaymentCountryProvider
{
    private PaymentMethodProviderInterface $paymentMethodProvider;
    private PaymentCountryNameProviderInterface $countryNameProvider;

    public function __construct(PaymentMethodProviderInterface $paymentMethodProvider, PaymentCountryNameProviderInterface $countryNameProvider)
    {
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->countryNameProvider = $countryNameProvider;
    }

    /**
     *  Returns an extended collection of payment countries including two special items:
     *   - "All Countries" — represents a generic option for selecting all available countries.
     *   - "Other Countries" — represents all countries not explicitly listed in the main collection.
     *
     *  If the collection contains two or more countries, the "All Countries" item is prepended.
     *  The "Other Countries" item is always appended at the end.
     *
     * @throws InvalidTypeException
     */
    public function getPaymentCountriesWithSpecialEntries(): PaymentCountryCollection
    {
        $paymentCountriesCollection = $this->getPaymentCountries();

        if ($paymentCountriesCollection->count() >= 2) {
            $paymentCountriesCollection->prepend(
                $this->createAllCountriesCountry()
            );
        }

        $paymentCountriesCollection->append($this->createOtherCountriesCountry());

        return $paymentCountriesCollection;
    }

    /**
     * @throws InvalidTypeException
     */
    public function getPaymentCountries(): PaymentCountryCollection
    {
        $paymentCountriesCollection = new PaymentCountryCollection();
        $paymentMethodCollection = $this->paymentMethodProvider->getPaymentMethods();

        if ($paymentMethodCollection->count() === 0) {
            return $paymentCountriesCollection;
        }

        foreach ($paymentMethodCollection as $paymentMethod) {
            $availableCountriesCollection = $paymentMethod->getAvailableCountries();

            if ($availableCountriesCollection->count() === 0) {
                continue;
            }

            foreach ($availableCountriesCollection as $country) {
                $country->setName(
                    $this->countryNameProvider->getCountryNameByCode($country->getCode())
                );

                $paymentCountriesCollection->append($country);
            }
        }

        $paymentCountriesCollection = $this->filterUniqueCountries($paymentCountriesCollection);

        return $this->sortCountries($paymentCountriesCollection);
    }

    private function filterUniqueCountries(PaymentCountryCollection $countriesCollection): PaymentCountryCollection
    {
        return $countriesCollection->uniqueBy(static fn (PaymentCountry $country) => $country->getCode());
    }

    private function sortCountries(PaymentCountryCollection $countriesCollection): PaymentCountryCollection
    {
        return $countriesCollection->sort(
            static fn (PaymentCountry $a, PaymentCountry $b) => strcoll($a->getName(), $b->getName())
        );
    }

    private function createAllCountriesCountry(): PaymentCountry
    {
        return new PaymentCountry(
            PaymentCountry::ALL_COUNTRIES_CODE,
            $this->countryNameProvider->getCountryNameByCode(PaymentCountry::ALL_COUNTRIES_CODE)
        );
    }

    private function createOtherCountriesCountry(): PaymentCountry
    {
        return new PaymentCountry(
            PaymentCountry::OTHER_COUNTRIES_CODE,
            $this->countryNameProvider->getCountryNameByCode(PaymentCountry::OTHER_COUNTRIES_CODE)
        );
    }
}
