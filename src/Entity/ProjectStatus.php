<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

class ProjectStatus implements EnumInterface
{
    public const DRAFT = 'draft';
    public const IN_REVIEW = 'in_review';
    public const NEEDS_CORRECTION = 'needs_correction';
    public const ACTIVATED = 'activated';
    public const BLOCKED = 'blocked';
    public const SUSPENDED = 'suspended';
    public const DELETED = 'deleted';

    public const STATUSES = [
        self::DRAFT,
        self::IN_REVIEW,
        self::NEEDS_CORRECTION,
        self::ACTIVATED,
        self::BLOCKED,
        self::SUSPENDED,
        self::DELETED,
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
