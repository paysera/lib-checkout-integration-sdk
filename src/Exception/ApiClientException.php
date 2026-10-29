<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Exception;

class ApiClientException extends BaseException
{
    protected function getDefaultCode(): int
    {
        return static::E_API_CLIENT;
    }
}
