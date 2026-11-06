<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

class OrderTemplate
{
    private string $id;
    private ?string $name;

    public function __construct(string $id, ?string $name = null)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Null when Paysera could not resolve the name of the reusable payment link.
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}
