<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\JWT;

use Firebase\JWT\ExpiredException;
use Paysera\CheckoutSdk\Exception\JwtValidationException;

interface PaymentApiJwtValidatorInterface
{
    /**
     * @throws JwtValidationException
     * @throws ExpiredException
     */
    public function validate(string $jwt): void;
}
