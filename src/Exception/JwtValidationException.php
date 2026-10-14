<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Exception;

class JwtValidationException extends BaseException
{
    protected function getDefaultMessage(): string
    {
        return 'JWT token validation failed';
    }

    protected function getDefaultCode(): int
    {
        return static::E_JWT_VALIDATION;
    }
}
