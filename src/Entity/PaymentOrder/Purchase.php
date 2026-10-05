<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\PaymentOrder;

class Purchase
{
    private string $reference;
    private int $amount;
    private string $currency;

    public function __construct(
        string $reference,
        int $amount,
        string $currency
    ) {
        $this->reference = $reference;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}
