<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use Paysera\CheckoutSdk\Util\SensitiveValue;

class PaymentApiCredentials
{
    private string $clientId;
    private SensitiveValue $clientSecret;

    public function __construct(
        string $clientId,
        string $clientSecret
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = new SensitiveValue($clientSecret);
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret->get();
    }
}
