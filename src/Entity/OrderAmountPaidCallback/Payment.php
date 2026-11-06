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
    private ?int $tipAmount;

    /**
     * @param int|null $tipAmount minor units of the payment currency, included in the payment amount
     */
    public function __construct(
        string $id,
        PaymentInfo $paymentInfo,
        PurchaseInfo $purchaseInfo,
        PayerInfo $payerInfo,
        ?int $tipAmount = null
    ) {
        $this->id = $id;
        $this->paymentInfo = $paymentInfo;
        $this->purchaseInfo = $purchaseInfo;
        $this->payerInfo = $payerInfo;
        $this->tipAmount = $tipAmount;
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

    /**
     * The payer's tip, in the payment currency. It is included in the purchase info payment amount.
     */
    public function getTipAmount(): ?int
    {
        return $this->tipAmount;
    }
}
