<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Provider;

use Paysera\CheckoutSdk\Entity\Collection\PaymentMethodCollection;

interface PaymentMethodProviderInterface
{
    public function getPaymentMethods(): PaymentMethodCollection;
}
