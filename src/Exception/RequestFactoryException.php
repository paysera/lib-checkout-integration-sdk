<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Exception;

class RequestFactoryException extends BaseException
{
    protected function getDefaultCode(): int
    {
        return static::E_REQUEST_FACTORY;
    }
}
