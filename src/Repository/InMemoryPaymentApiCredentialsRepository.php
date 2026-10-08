<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Repository;

use Paysera\CheckoutSdk\Entity\PaymentApiCredentials;

class InMemoryPaymentApiCredentialsRepository implements PaymentApiCredentialsRepositoryInterface
{
    private ?PaymentApiCredentials $apiCredentials;

    public function __construct()
    {
        $this->apiCredentials = null;
    }

    public function save(PaymentApiCredentials $apiCredentials): void
    {
        $this->apiCredentials = $apiCredentials;
    }

    public function retrieve(): ?PaymentApiCredentials
    {
        return $this->apiCredentials;
    }
}
