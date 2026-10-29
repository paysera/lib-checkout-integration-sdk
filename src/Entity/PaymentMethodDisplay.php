<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

class PaymentMethodDisplay
{
    private string $title;
    private string $logoUrl;
    private bool $showTitle;

    public function __construct(string $title, string $logoUrl, bool $showTitle)
    {
        $this->title = $title;
        $this->logoUrl = $logoUrl;
        $this->showTitle = $showTitle;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getLogoUrl(): string
    {
        return $this->logoUrl;
    }

    public function shouldShowTitle(): bool
    {
        return $this->showTitle;
    }
}
