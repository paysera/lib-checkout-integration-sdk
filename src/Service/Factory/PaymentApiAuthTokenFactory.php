<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Factory;

use Paysera\CheckoutSdk\Entity\PaymentApiAuthToken;
use Paysera\CheckoutSdk\Service\PaymentApiConfiguration;

class PaymentApiAuthTokenFactory
{
    private PaymentApiConfiguration $paymentApiConfiguration;

    public function __construct(
        PaymentApiConfiguration $paymentApiConfiguration
    ) {
        $this->paymentApiConfiguration = $paymentApiConfiguration;
    }

    public function create(string $accessToken): PaymentApiAuthToken
    {
        return new PaymentApiAuthToken(
            $this->paymentApiConfiguration->getPaymentApiEnvironment(),
            $accessToken,
        );
    }
}
