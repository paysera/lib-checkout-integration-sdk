<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Exception;

class RuntimeException extends BaseException
{
    protected function getDefaultCode(): int
    {
        return static::E_RUNTIME;
    }
}
