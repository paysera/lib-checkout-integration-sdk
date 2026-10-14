<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service;

use Paysera\CheckoutSdk\Entity\PaymentApiEnvironment;

class PaymentApiConfiguration
{
    private PaymentApiEnvironment $paymentApiEnvironment;

    public function __construct(PaymentApiEnvironment $paymentApiEnvironment)
    {
        $this->paymentApiEnvironment = $paymentApiEnvironment;
    }

    public function getPaymentApiEnvironment(): PaymentApiEnvironment
    {
        return $this->paymentApiEnvironment;
    }
}
