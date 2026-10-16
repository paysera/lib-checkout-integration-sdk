<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\RefundOrder;

use Paysera\CheckoutSdk\Entity\PaymentLink\PayerInformation;

class RefundDetails
{
    private string $orderId;
    private string $reference;
    private int $refundAmount;
    private string $currency;
    private ?string $reason;
    private PayerInformation $payer;
    private ?SelectedRefundMethod $refundMethod;

    public function __construct(
        string $orderId,
        string $reference,
        int $refundAmount,
        string $currency,
        PayerInformation $payer,
        ?string $reason = null,
        ?SelectedRefundMethod $refundMethod = null
    ) {
        $this->orderId = $orderId;
        $this->reference = $reference;
        $this->refundAmount = $refundAmount;
        $this->currency = $currency;
        $this->reason = $reason;
        $this->payer = $payer;
        $this->refundMethod = $refundMethod;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getRefundAmount(): int
    {
        return $this->refundAmount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function getPayer(): PayerInformation
    {
        return $this->payer;
    }

    public function getRefundMethod(): ?SelectedRefundMethod
    {
        return $this->refundMethod;
    }
}
