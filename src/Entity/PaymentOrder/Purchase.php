<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\PaymentOrder;

class Purchase
{
    private string $reference;
    private ?int $amount;
    private string $currency;
    private bool $payerSetsAmount;
    private ?int $minimumAmount;
    private ?int $maximumAmount;
    private ?int $suggestedAmount;
    /** @var list<int> */
    private array $amountButtons;

    /**
     * @param int|null $amount minor units; leave null when $payerSetsAmount is true
     * @param int|null $minimumAmount minor units; only with $payerSetsAmount
     * @param int|null $maximumAmount minor units; only with $payerSetsAmount
     * @param int|null $suggestedAmount minor units prefilled for the payer; only with $payerSetsAmount
     * @param array<int> $amountButtons 3 or 4 ascending preset amounts in minor units; only with $payerSetsAmount
     */
    public function __construct(
        string $reference,
        ?int $amount,
        string $currency,
        bool $payerSetsAmount = false,
        ?int $minimumAmount = null,
        ?int $maximumAmount = null,
        ?int $suggestedAmount = null,
        array $amountButtons = []
    ) {
        $this->reference = $reference;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->payerSetsAmount = $payerSetsAmount;
        $this->minimumAmount = $minimumAmount;
        $this->maximumAmount = $maximumAmount;
        $this->suggestedAmount = $suggestedAmount;
        $this->amountButtons = array_values($amountButtons);
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * Null while the payer sets the amount and has not entered it yet.
     */
    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function isPayerSetsAmount(): bool
    {
        return $this->payerSetsAmount;
    }

    public function getMinimumAmount(): ?int
    {
        return $this->minimumAmount;
    }

    public function getMaximumAmount(): ?int
    {
        return $this->maximumAmount;
    }

    public function getSuggestedAmount(): ?int
    {
        return $this->suggestedAmount;
    }

    /**
     * @return list<int>
     */
    public function getAmountButtons(): array
    {
        return $this->amountButtons;
    }
}
