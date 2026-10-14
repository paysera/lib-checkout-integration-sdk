<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\PaymentLink;

class Purchase
{
    private ?int $amount;
    private bool $payerSetsAmount = false;
    private ?int $minimumAmount = null;
    private ?int $maximumAmount = null;
    private ?int $suggestedAmount;
    /** @var list<int> */
    private array $amountButtons;

    /**
     * @param int|null $amount minor units; leave null for a link to a payer-set order
     * @param int|null $suggestedAmount minor units; overrides the order's suggested amount, payer-set orders only
     * @param array<int> $amountButtons minor units; overrides the order's amount buttons, payer-set orders only
     */
    public function __construct(?int $amount = null, ?int $suggestedAmount = null, array $amountButtons = [])
    {
        $this->amount = $amount;
        $this->suggestedAmount = $suggestedAmount;
        $this->amountButtons = array_values($amountButtons);
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(?int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getSuggestedAmount(): ?int
    {
        return $this->suggestedAmount;
    }

    public function setSuggestedAmount(?int $suggestedAmount): self
    {
        $this->suggestedAmount = $suggestedAmount;

        return $this;
    }

    /**
     * @return list<int>
     */
    public function getAmountButtons(): array
    {
        return $this->amountButtons;
    }

    /**
     * @param array<int> $amountButtons
     */
    public function setAmountButtons(array $amountButtons): self
    {
        $this->amountButtons = array_values($amountButtons);

        return $this;
    }

    /**
     * Read from the create response: the link takes these from its order and never sends them.
     */
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

    /**
     * @param int|null $minimumAmount minor units; null when the order has no minimum
     * @param int|null $maximumAmount minor units; null when the order has no maximum
     */
    public function setPayerSetAmount(bool $payerSetsAmount, ?int $minimumAmount, ?int $maximumAmount): self
    {
        $this->payerSetsAmount = $payerSetsAmount;
        $this->minimumAmount = $minimumAmount;
        $this->maximumAmount = $maximumAmount;

        return $this;
    }
}
