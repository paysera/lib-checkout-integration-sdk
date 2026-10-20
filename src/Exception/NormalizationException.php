<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Exception;

class NormalizationException extends BaseException
{
    protected function getDefaultMessage(): string
    {
        return 'Normalization failed';
    }

    protected function getDefaultCode(): int
    {
        return static::E_NORMALIZATION;
    }
}
