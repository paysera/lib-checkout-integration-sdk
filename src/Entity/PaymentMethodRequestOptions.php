<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

final class PaymentMethodRequestOptions
{
    private ?string $locale;

    public function __construct(?string $locale = null)
    {
        $this->locale = $locale;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }
}
