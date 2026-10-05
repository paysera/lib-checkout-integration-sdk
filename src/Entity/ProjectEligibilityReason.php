<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use Paysera\CheckoutSdk\Entity\Collection\ItemInterface;

class ProjectEligibilityReason implements EnumInterface, ItemInterface
{
    public const STORE_URL_NOT_CONFIRMED = 'store_url_not_confirmed';
    public const PROJECT_NOT_SUBMITTED_FOR_REVIEW = 'project_not_submitted_for_review';
    public const PAYMENT_COLLECTION_DISABLED = 'payment_collection_disabled';

    public const REASONS = [
        self::STORE_URL_NOT_CONFIRMED,
        self::PROJECT_NOT_SUBMITTED_FOR_REVIEW,
        self::PAYMENT_COLLECTION_DISABLED,
    ];

    private string $reason;

    public function __construct(string $reason)
    {
        $this->reason = $reason;
    }

    public function getValue(): string
    {
        return $this->reason;
    }
}
