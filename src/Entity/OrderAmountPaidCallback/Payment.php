<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback;

use Paysera\CheckoutSdk\Entity\Collection\ItemInterface;

class Payment implements ItemInterface
{
    private string $id;
    private PaymentInfo $paymentInfo;
    private PurchaseInfo $purchaseInfo;
    private PayerInfo $payerInfo;

    public function __construct(
        string $id,
        PaymentInfo $paymentInfo,
        PurchaseInfo $purchaseInfo,
        PayerInfo $payerInfo
    ) {
        $this->id = $id;
        $this->paymentInfo = $paymentInfo;
        $this->purchaseInfo = $purchaseInfo;
        $this->payerInfo = $payerInfo;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPaymentInfo(): PaymentInfo
    {
        return $this->paymentInfo;
    }

    public function getPurchaseInfo(): PurchaseInfo
    {
        return $this->purchaseInfo;
    }

    public function getPayerInfo(): PayerInfo
    {
        return $this->payerInfo;
    }
}
