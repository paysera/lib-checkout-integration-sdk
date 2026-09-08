<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback;

use Paysera\CheckoutSdk\Entity\Collection\ItemInterface;

class PaymentLink implements ItemInterface
{
    private string $id;
    private string $name;
    private Timestamps $timestamps;
    private PayerInfo $payerInfo;
    private PaymentCollection $payments;

    public function __construct(
        string $id,
        string $name,
        Timestamps $timestamps,
        PayerInfo $payerInfo,
        PaymentCollection $payments
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->timestamps = $timestamps;
        $this->payerInfo = $payerInfo;
        $this->payments = $payments;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTimestamps(): Timestamps
    {
        return $this->timestamps;
    }

    public function getPayerInfo(): PayerInfo
    {
        return $this->payerInfo;
    }

    /**
     * @return PaymentCollection<Payment>
     */
    public function getPayments(): PaymentCollection
    {
        return $this->payments;
    }
}
