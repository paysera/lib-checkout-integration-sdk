<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use Paysera\CheckoutSdk\Exception\ValidationException;

class PaymentMethodDisplayOptions
{
    private ?string $mode;
    private bool $countryLogosEnabled;

    /**
     * @throws ValidationException
     */
    public function __construct(?string $mode = null, bool $countryLogosEnabled = false)
    {
        $this->validateMode($mode);

        $this->mode = $mode;
        $this->countryLogosEnabled = $countryLogosEnabled;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function isCountryLogosEnabled(): bool
    {
        return $this->countryLogosEnabled;
    }

    private function validateMode(?string $mode): void
    {
        if ($mode === null || in_array($mode, PaymentMethodDisplayMode::MODES, true)) {
            return;
        }

        throw (new ValidationException(
            'Payment method display mode must be one of: ' . implode(', ', PaymentMethodDisplayMode::MODES) . '.'
        ))
            ->setContext(['mode' => $mode])
        ;
    }
}
