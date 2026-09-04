<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

class CallbackEvent implements CallbackEventInterface, EnumInterface
{
    public const TYPE_ORDER = 'order';
    public const NAME_AMOUNT_PAID_UPDATED = 'amount_paid_updated';

    private string $name;
    private string $type;

    public function __construct(
        string $name,
        string $type
    ) {
        $this->name = $name;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return sprintf('%s:%s', $this->type, $this->name);
    }
}
