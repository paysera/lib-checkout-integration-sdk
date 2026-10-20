<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

class ProjectEligibilityStatus implements EnumInterface
{
    public const ELIGIBLE = 'eligible';
    public const INELIGIBLE = 'ineligible';
    public const FAILED_TO_CHECK = 'failed_to_check';

    public const STATUSES = [
        self::ELIGIBLE,
        self::INELIGIBLE,
        self::FAILED_TO_CHECK,
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
