<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use Paysera\CheckoutSdk\Exception\InvalidArgumentException;
use Stringable;

class PaymentApiEnvironment implements EnumInterface, Stringable
{
    public const PRODUCTION_ENVIRONMENT = 'production';
    public const SANDBOX_ENVIRONMENT = 'sandbox';
    public const ENVIRONMENTS = [
        self::PRODUCTION_ENVIRONMENT,
        self::SANDBOX_ENVIRONMENT,
    ];

    private string $environment = self::PRODUCTION_ENVIRONMENT;

    /**
     * @throws InvalidArgumentException
     */
    public static function createFromString(string $environment): self
    {
        if (!in_array($environment, self::ENVIRONMENTS, true)) {
            throw new InvalidArgumentException('Payment API environment "' . $environment . '" does not exists');
        }

        $paymentApiEnvironment = new self();

        if ($environment === self::PRODUCTION_ENVIRONMENT) {
            return $paymentApiEnvironment->enableProductionEnvironment();
        }

        return $paymentApiEnvironment->enableSandboxEnvironment();
    }

    public function getValue(): string
    {
        return $this->environment;
    }

    public function isProduction(): bool
    {
        return $this->environment === self::PRODUCTION_ENVIRONMENT;
    }

    public function isSandbox(): bool
    {
        return $this->environment === self::SANDBOX_ENVIRONMENT;
    }

    public function enableProductionEnvironment(): self
    {
        $this->environment = self::PRODUCTION_ENVIRONMENT;

        return $this;
    }

    public function enableSandboxEnvironment(): self
    {
        $this->environment = self::SANDBOX_ENVIRONMENT;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
