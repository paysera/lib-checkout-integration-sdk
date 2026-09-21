<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use DateTimeImmutable;
use Paysera\CheckoutSdk\Entity\PaymentOrder\Purchase;

class PaymentOrderCreateResponse
{
    private string $projectId;
    private string $orderId;
    private DateTimeImmutable $createdAt;
    private Metadata $metadata;
    private string $source;
    private Purchase $purchase;
    private bool $isTest;

    public function __construct(
        string $projectId,
        string $orderId,
        DateTimeImmutable $createdAt,
        Metadata $metadata,
        string $source,
        Purchase $purchase,
        bool $isTest = false
    ) {
        $this->projectId = $projectId;
        $this->orderId = $orderId;
        $this->createdAt = $createdAt;
        $this->metadata = $metadata;
        $this->source = $source;
        $this->purchase = $purchase;
        $this->isTest = $isTest;
    }

    public function getProjectId(): string
    {
        return $this->projectId;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getPurchase(): Purchase
    {
        return $this->purchase;
    }

    /**
     * Whether Paysera settled this order as a test payment. The flag is decided by the engine when the
     * order is created, so it describes this order and not the project's current test-mode setting.
     */
    public function isTest(): bool
    {
        return $this->isTest;
    }

    public function getLoggerData(): array
    {
        return [
            'project_id' => $this->getProjectId(),
            'order_id' => $this->getOrderId(),
            'reference' => $this->getPurchase()->getReference(),
            'amount' => $this->getPurchase()->getAmount(),
            'currency' => $this->getPurchase()->getCurrency(),
            'source' => $this->getSource(),
            'is_test' => $this->isTest(),
        ];
    }
}
