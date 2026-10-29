<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Exception;

class ValidationException extends BaseException
{
    protected function getDefaultMessage(): string
    {
        return 'Validation Error';
    }

    protected function getDefaultCode(): int
    {
        return static::E_VALIDATION;
    }
}
