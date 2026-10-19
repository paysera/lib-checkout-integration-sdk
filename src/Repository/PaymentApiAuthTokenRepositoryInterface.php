<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Repository;

use Paysera\CheckoutSdk\Entity\PaymentApiAuthToken;

interface PaymentApiAuthTokenRepositoryInterface
{
    public function save(PaymentApiAuthToken $authToken): void;

    public function retrieve(): ?PaymentApiAuthToken;
}
