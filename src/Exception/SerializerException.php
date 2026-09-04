<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Exception;

class SerializerException extends BaseException
{
    protected function getDefaultMessage(): string
    {
        return 'Serialization failed';
    }

    protected function getDefaultCode(): int
    {
        return static::E_SERIALIZATION;
    }
}
