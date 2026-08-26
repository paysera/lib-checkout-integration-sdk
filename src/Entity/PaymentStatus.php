<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use Paysera\CheckoutSdk\Entity\Collection\ItemInterface;
use Paysera\CheckoutSdk\Exception\PaymentStatusValidationException;

class PaymentStatus implements ItemInterface, EnumInterface
{
    public const AWAITING_AUTHORIZATION = 'awaiting_authorization';
    public const AUTHORIZED = 'authorized';
    public const PROCESSING = 'processing';
    public const PENDING_SETTLEMENT = 'pending_settlement';
    public const ON_HOLD = 'on_hold';
    public const SETTLED = 'settled';
    public const FAILED = 'failed';
    public const REJECTED = 'rejected';
    public const CANCELLED = 'cancelled';
    public const EXPIRED = 'expired';
    public const REFUNDED = 'refunded';
    public const CHARGEBACK = 'chargeback';
    public const INITIATED = 'initiated';

    public const STATUSES = [
        self::AWAITING_AUTHORIZATION,
        self::AUTHORIZED,
        self::PROCESSING,
        self::PENDING_SETTLEMENT,
        self::ON_HOLD,
        self::SETTLED,
        self::FAILED,
        self::REJECTED,
        self::CANCELLED,
        self::EXPIRED,
        self::REFUNDED,
        self::CHARGEBACK,
        self::INITIATED,
    ];

    private string $status;

    public function __construct(string $status)
    {
        $this->status = $status;
    }

    public function getValue(): string
    {
        return $this->status;
    }
}
