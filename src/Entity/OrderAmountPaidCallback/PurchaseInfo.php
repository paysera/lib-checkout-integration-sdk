<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback;

class PurchaseInfo
{
    private string $paymentCurrency;
    private int $paymentAmount;
    private ?int $originalAmount;
    private ?string $originalCurrency;

    public function __construct(string $paymentCurrency, int $paymentAmount)
    {
        $this->paymentCurrency = $paymentCurrency;
        $this->paymentAmount = $paymentAmount;
        $this->originalAmount = null;
        $this->originalCurrency = null;
    }

    public function getPaymentCurrency(): string
    {
        return $this->paymentCurrency;
    }

    public function getPaymentAmount(): int
    {
        return $this->paymentAmount;
    }

    public function getOriginalAmount(): ?int
    {
        return $this->originalAmount;
    }

    public function setOriginalAmount(?int $originalAmount): self
    {
        $this->originalAmount = $originalAmount;

        return $this;
    }

    public function getOriginalCurrency(): ?string
    {
        return $this->originalCurrency;
    }

    public function setOriginalCurrency(?string $originalCurrency): self
    {
        $this->originalCurrency = $originalCurrency;

        return $this;
    }
}
