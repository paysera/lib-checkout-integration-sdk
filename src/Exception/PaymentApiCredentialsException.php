<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Exception;

class PaymentApiCredentialsException extends BaseException
{
    protected function getDefaultCode(): int
    {
        return static::E_RUNTIME;
    }
}
