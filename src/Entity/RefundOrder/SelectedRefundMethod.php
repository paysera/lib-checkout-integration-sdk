<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\RefundOrder;

class SelectedRefundMethod
{
    private string $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
