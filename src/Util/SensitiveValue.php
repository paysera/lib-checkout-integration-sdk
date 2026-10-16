<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Util;

use Closure;
use Paysera\CheckoutSdk\Exception\RuntimeException;

final class SensitiveValue
{
    private Closure $value;

    public function __construct($value)
    {
        $this->value = static fn () => $value;
    }

    public function get()
    {
        return $this->value->__invoke();
    }

    public function isEmpty(): bool
    {
        return $this->get() === null;
    }

    public function __serialize(): array
    {
        return [];
    }

    public function __unserialize(array $data): void
    {
    }

    public function __toString(): string
    {
        return '***SENSITIVE VALUE***';
    }

    /**
     * @throws RuntimeException
     */
    public function __clone()
    {
        throw new RuntimeException('It is not permitted to clone this object');
    }

    public function __debugInfo(): array
    {
        return [
            'value' => '***SENSITIVE VALUE***',
        ];
    }
}
