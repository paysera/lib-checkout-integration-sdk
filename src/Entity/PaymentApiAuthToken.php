<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use Paysera\CheckoutSdk\Exception\InvalidArgumentException;
use Paysera\CheckoutSdk\Util\SensitiveValue;
use Stringable;

class PaymentApiAuthToken implements Stringable
{
    private PaymentApiEnvironment $environment;
    private SensitiveValue $accessToken;

    public function __construct(
        PaymentApiEnvironment $environment,
        string $accessToken
    ) {
        $this->environment = $environment;
        $this->accessToken = new SensitiveValue($accessToken);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function createFromString(string $accessToken, string $environment): self
    {
        $paymentApiEnvironment = PaymentApiEnvironment::createFromString($environment);

        return new self($paymentApiEnvironment, $accessToken);
    }

    public function getEnvironment(): PaymentApiEnvironment
    {
        return $this->environment;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken->get();
    }

    public function __toString(): string
    {
        return $this->accessToken->get();
    }
}
