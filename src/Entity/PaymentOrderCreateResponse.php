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

    public function __construct(
        string $projectId,
        string $orderId,
        DateTimeImmutable $createdAt,
        Metadata $metadata,
        string $source,
        Purchase $purchase
    ) {
        $this->projectId = $projectId;
        $this->orderId = $orderId;
        $this->createdAt = $createdAt;
        $this->metadata = $metadata;
        $this->source = $source;
        $this->purchase = $purchase;
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

    public function getLoggerData(): array
    {
        return [
            'project_id' => $this->getProjectId(),
            'order_id' => $this->getOrderId(),
            'reference' => $this->getPurchase()->getReference(),
            'amount' => $this->getPurchase()->getAmount(),
            'currency' => $this->getPurchase()->getCurrency(),
            'source' => $this->getSource(),
        ];
    }
}
