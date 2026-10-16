<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use Paysera\CheckoutSdk\Entity\Collection\ItemInterface;

class PaymentCountry implements ItemInterface
{
    public const ALL_COUNTRIES_CODE = 'all_countries';
    public const OTHER_COUNTRIES_CODE = 'other_countries';

    private string $code;
    private string $name;

    public function __construct(string $code, ?string $name = null)
    {
        $this->code = $code;
        $this->name = $name ?? $code;
    }

    public function setCode(string $code): self
    {
        $this->code = strtoupper($code);

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
