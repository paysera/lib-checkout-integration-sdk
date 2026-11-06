<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use DateTimeImmutable;

class RefundOrderResponse
{
    private string $status;
    private string $refundId;
    private int $amount;
    private string $currency;
    private ?DateTimeImmutable $createdAt;
    private string $reference;

    public function __construct(
        string $status,
        string $refundId,
        int $amount,
        string $currency,
        ?DateTimeImmutable $createdAt,
        string $reference
    ) {
        $this->status = $status;
        $this->refundId = $refundId;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->createdAt = $createdAt;
        $this->reference = $reference;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getRefundId(): string
    {
        return $this->refundId;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getLoggerData(): array
    {
        return [
            'refund_id' => $this->getRefundId(),
            'reference' => $this->getReference(),
        ];
    }
}
