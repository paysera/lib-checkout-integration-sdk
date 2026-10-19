<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Repository;

use Paysera\CheckoutSdk\Entity\PaymentApiCredentials;

interface PaymentApiCredentialsRepositoryInterface
{
    public function save(PaymentApiCredentials $apiCredentials): void;

    public function retrieve(): ?PaymentApiCredentials;
}
