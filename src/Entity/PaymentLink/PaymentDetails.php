<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\PaymentLink;

class PaymentDetails
{
    private ?string $key;
    private ?string $purpose;
    private ?string $countryCode;

    public function __construct(
        ?string $key = null,
        ?string $purpose = null,
        ?string $countryCode = null
    ) {
        $this->key = $key;
        $this->purpose = $purpose;
        $this->countryCode = $countryCode;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getPurpose(): ?string
    {
        return $this->purpose;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }
}
