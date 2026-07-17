<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use Paysera\CheckoutSdk\Entity\Collection\ItemInterface;
use Paysera\CheckoutSdk\Entity\Collection\PaymentCountryCollection;

class PaymentMethod implements ItemInterface
{
    public const FLOW_REDIRECT = 'redirect';
    public const FLOW_DIRECT = 'direct';

    private string $key;
    private string $title;
    private string $description;
    private string $type;
    private string $flow;
    private PaymentCountryCollection $availableCountries;
    private string $logoUrl;

    public function __construct(
        string $key,
        string $title,
        string $description,
        string $type,
        string $flow,
        PaymentCountryCollection $availableCountries,
        string $logoUrl = ''
    ) {
        $this->key = $key;
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->flow = $flow;
        $this->availableCountries = $availableCountries;
        $this->logoUrl = $logoUrl;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getFlow(): string
    {
        return $this->flow;
    }

    public function getAvailableCountries(): PaymentCountryCollection
    {
        return $this->availableCountries;
    }

    public function getLogoUrl(): string
    {
        return $this->logoUrl;
    }

    /**
     * @deprecated since the backend now returns `logo_url`; use getLogoUrl() instead.
     */
    public function getIconUrl(): string
    {
        return $this->getLogoUrl();
    }
}
