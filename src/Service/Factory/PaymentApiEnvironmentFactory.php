<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Factory;

use Paysera\CheckoutSdk\Entity\PaymentApiEnvironment;
use Paysera\CheckoutSdk\Exception\InvalidArgumentException;

class PaymentApiEnvironmentFactory
{
    /**
     * @throws InvalidArgumentException
     */
    public function create(string $environment): PaymentApiEnvironment
    {
        return PaymentApiEnvironment::createFromString($environment);
    }
}
