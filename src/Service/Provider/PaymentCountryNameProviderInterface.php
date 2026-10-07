<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Provider;

interface PaymentCountryNameProviderInterface
{
    public function getCountryNameByCode(string $countryCode): string;
}
