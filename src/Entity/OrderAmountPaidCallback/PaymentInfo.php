<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback;

class PaymentInfo
{
    private string $method;
    private string $status;
    private Timestamps $timestamps;
    private string $purpose;
    private ?string $paymentCountry;

    public function __construct(
        string $method,
        string $status,
        Timestamps $timestamps,
        string $purpose
    ) {
        $this->method = $method;
        $this->status = $status;
        $this->timestamps = $timestamps;
        $this->purpose = $purpose;
        $this->paymentCountry = null;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTimestamps(): Timestamps
    {
        return $this->timestamps;
    }

    public function getPurpose(): string
    {
        return $this->purpose;
    }

    public function getPaymentCountry(): ?string
    {
        return $this->paymentCountry;
    }

    public function setPaymentCountry(?string $paymentCountry): self
    {
        $this->paymentCountry = $paymentCountry;

        return $this;
    }
}
