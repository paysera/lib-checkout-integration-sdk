<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

class PaymentMethodCountryDisplay
{
    private string $countryCode;
    private ?string $title;
    private ?string $logoUrl;
    private ?string $logoWideUrl;

    public function __construct(
        string $countryCode,
        ?string $title = null,
        ?string $logoUrl = null,
        ?string $logoWideUrl = null
    ) {
        $this->countryCode = strtoupper(trim($countryCode));
        $this->title = $title;
        $this->logoUrl = $logoUrl;
        $this->logoWideUrl = $logoWideUrl;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function getLogoWideUrl(): ?string
    {
        return $this->logoWideUrl;
    }
}
