<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use Paysera\CheckoutSdk\Exception\ValidationException;

/**
 * Value Object representing payment method filtering criteria.
 *
 * Both amount and currency must be provided together to filter payment methods
 * whose limits encompass the specified amount.
 */
class PaymentMethodFilter
{
    private int $amount;
    private string $currency;

    /**
     * @param int $amount Transaction amount in cents (must be greater than 0)
     * @param string $currency ISO 4217 currency code (e.g., "EUR", "USD")
     *
     * @throws ValidationException If amount or currency is invalid
     */
    public function __construct(int $amount, string $currency)
    {
        $this->validateAmount($amount);
        $this->validateCurrency($currency);

        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    private function validateAmount(int $amount): void
    {
        if ($amount <= 0) {
            throw (new ValidationException('Amount must be greater than 0'))
                ->setContext(['amount' => $amount])
            ;
        }
    }

    private function validateCurrency(string $currency): void
    {
        if (!in_array($currency, PaymentCurrency::SUPPORTED_CURRENCIES, true)) {
            throw (new ValidationException(
                'Currency must be one of: ' . implode(', ', PaymentCurrency::SUPPORTED_CURRENCIES) . '.'
            ))
                ->setContext(['currency' => $currency])
            ;
        }
    }
}
