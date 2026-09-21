<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Exception;

class PaymentApiAuthTokenException extends BaseException
{
    protected function getDefaultCode(): int
    {
        return static::E_AUTH_TOKEN;
    }
}
