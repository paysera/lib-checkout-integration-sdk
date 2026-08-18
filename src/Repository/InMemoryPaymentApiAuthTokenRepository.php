<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Repository;

use Paysera\CheckoutSdk\Entity\PaymentApiAuthToken;

class InMemoryPaymentApiAuthTokenRepository implements PaymentApiAuthTokenRepositoryInterface
{
    private ?PaymentApiAuthToken $authToken;

    public function __construct()
    {
        $this->authToken = null;
    }

    public function save(PaymentApiAuthToken $authToken): void
    {
        $this->authToken = $authToken;
    }

    public function retrieve(): ?PaymentApiAuthToken
    {
        return $this->authToken;
    }
}
