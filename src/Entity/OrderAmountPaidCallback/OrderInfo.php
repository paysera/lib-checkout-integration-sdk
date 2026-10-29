<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback;

class OrderInfo
{
    public const ORDER_STATUS_PAID = 'paid';

    private string $merchantOrderId;
    private string $source;
    private int $amount;
    private int $amountPaid;
    private string $currency;
    private string $status;

    public function __construct(
        string $merchantOrderId,
        string $source,
        int $amount,
        int $amountPaid,
        string $currency,
        string $status
    ) {
        $this->merchantOrderId = $merchantOrderId;
        $this->source = $source;
        $this->amount = $amount;
        $this->amountPaid = $amountPaid;
        $this->currency = $currency;
        $this->status = $status;
    }

    public function getMerchantOrderId(): string
    {
        return $this->merchantOrderId;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getAmountPaid(): int
    {
        return $this->amountPaid;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isPaid(): bool
    {
        return strtolower($this->status) === self::ORDER_STATUS_PAID;
    }
}
