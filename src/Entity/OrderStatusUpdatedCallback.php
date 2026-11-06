<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

class OrderStatusUpdatedCallback implements CallbackInterface
{
    public const STATUS_CANCELLED = 'cancelled';

    public const REASON_EXPIRED_TTL = 'expired_ttl';
    public const REASON_CANCELED_BY_MERCHANT = 'canceled_by_merchant';
    public const REASON_CANCELED_BY_ADMIN = 'canceled_by_admin';
    public const REASON_PROJECT_DELETED = 'project_deleted';
    public const REASON_ORDER_TEMPLATE_DEACTIVATED = 'order_template_deactivated';

    private CallbackEventInterface $event;
    private string $payseraOrderId;
    private string $merchantOrderId;
    private string $status;
    private ?string $reason;
    private bool $isTest;
    private ?int $timestamp;
    private ?OrderTemplate $orderTemplate;

    public function __construct(
        CallbackEventInterface $event,
        string $payseraOrderId,
        string $merchantOrderId,
        string $status,
        ?string $reason,
        bool $isTest,
        ?int $timestamp,
        ?OrderTemplate $orderTemplate = null
    ) {
        $this->event = $event;
        $this->payseraOrderId = $payseraOrderId;
        $this->merchantOrderId = $merchantOrderId;
        $this->status = $status;
        $this->reason = $reason;
        $this->isTest = $isTest;
        $this->timestamp = $timestamp;
        $this->orderTemplate = $orderTemplate;
    }

    public function getEvent(): CallbackEventInterface
    {
        return $this->event;
    }

    public function getPayseraOrderId(): string
    {
        return $this->payseraOrderId;
    }

    public function getMerchantOrderId(): string
    {
        return $this->merchantOrderId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function isTest(): bool
    {
        return $this->isTest;
    }

    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }

    /**
     * The reusable payment link the order was opened from; null for any other order.
     */
    public function getOrderTemplate(): ?OrderTemplate
    {
        return $this->orderTemplate;
    }
}
