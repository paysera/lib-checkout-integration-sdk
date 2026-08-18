<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use Paysera\CheckoutSdk\Entity\Collection\ItemInterface;

class RefundStatus implements ItemInterface, EnumInterface
{
    public const PROCESSING = 'processing';
    public const FAILED = 'failed';
    public const COMPLETED = 'completed';

    public const STATUSES = [
        self::PROCESSING,
        self::FAILED,
        self::COMPLETED,
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
