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

    public function __construct(
        string $id,
        OrderInfo $orderInfo,
        Timestamps $timestamps,
        MerchantData $merchantData,
        PaymentLinkCollection $paymentLinkCollection
    ) {
        $this->id = $id;
        $this->orderInfo = $orderInfo;
        $this->timestamps = $timestamps;
        $this->merchantData = $merchantData;
        $this->paymentLinkCollection = $paymentLinkCollection;
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
}
