<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\JWT;

use Firebase\JWT\ExpiredException;
use Paysera\CheckoutSdk\Exception\JwtValidationException;
use Paysera\CheckoutSdk\Util\SensitiveValue;

interface PaymentApiJwtDecoderInterface
{
    /**
     * @throws JwtValidationException
     * @throws ExpiredException
     */
    public function decode(string $jwt): SensitiveValue;
}
