<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback;

class PayerInfo
{
    private ?string $payerName;
    private ?string $payerEmail;
    private ?string $payerIpCountry;
    private ?string $payerCountry;

    public function __construct()
    {
        $this->payerName = null;
        $this->payerEmail = null;
        $this->payerIpCountry = null;
        $this->payerCountry = null;
    }

    public function getPayerName(): ?string
    {
        return $this->payerName;
    }

    public function setPayerName(?string $payerName): self
    {
        $this->payerName = $payerName;

        return $this;
    }

    public function getPayerEmail(): ?string
    {
        return $this->payerEmail;
    }

    public function setPayerEmail(?string $payerEmail): self
    {
        $this->payerEmail = $payerEmail;

        return $this;
    }

    public function getPayerIpCountry(): ?string
    {
        return $this->payerIpCountry;
    }

    public function setPayerIpCountry(?string $payerIpCountry): self
    {
        $this->payerIpCountry = $payerIpCountry;

        return $this;
    }

    public function getPayerCountry(): ?string
    {
        return $this->payerCountry;
    }

    public function setPayerCountry(?string $payerCountry): self
    {
        $this->payerCountry = $payerCountry;

        return $this;
    }
}
