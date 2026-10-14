<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback;

class Order
{
    private string $id;
    private OrderInfo $orderInfo;
    private Timestamps $timestamps;
    private MerchantData $merchantData;
    private PaymentLinkCollection $paymentLinkCollection;
    private bool $payerSetsAmount;
    private ?int $minimumAmount;
    private ?int $maximumAmount;

    /**
     * @param int|null $minimumAmount minor units; null for a fixed-amount order
     * @param int|null $maximumAmount minor units; null for a fixed-amount order
     */
    public function __construct(
        string $id,
        OrderInfo $orderInfo,
        Timestamps $timestamps,
        MerchantData $merchantData,
        PaymentLinkCollection $paymentLinkCollection,
        bool $payerSetsAmount = false,
        ?int $minimumAmount = null,
        ?int $maximumAmount = null
    ) {
        $this->id = $id;
        $this->orderInfo = $orderInfo;
        $this->timestamps = $timestamps;
        $this->merchantData = $merchantData;
        $this->paymentLinkCollection = $paymentLinkCollection;
        $this->payerSetsAmount = $payerSetsAmount;
        $this->minimumAmount = $minimumAmount;
        $this->maximumAmount = $maximumAmount;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOrderInfo(): OrderInfo
    {
        return $this->orderInfo;
    }

    public function getTimestamps(): Timestamps
    {
        return $this->timestamps;
    }

    public function getMerchantData(): MerchantData
    {
        return $this->merchantData;
    }

    /**
     * @return PaymentLinkCollection<PaymentLink>
     */
    public function getPaymentLinkCollection(): PaymentLinkCollection
    {
        return $this->paymentLinkCollection;
    }

    /**
     * True when the payer entered the amount; the order info amount is then the amount they entered.
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
}
