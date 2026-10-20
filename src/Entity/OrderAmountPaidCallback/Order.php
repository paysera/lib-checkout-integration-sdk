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
    private bool $tipsEnabled;
    private ?int $tipAmount;

    /**
     * @param int|null $minimumAmount minor units; null for a fixed-amount order
     * @param int|null $maximumAmount minor units; null for a fixed-amount order
     * @param int|null $tipAmount minor units of the order currency, included in the amount paid
     */
    public function __construct(
        string $id,
        OrderInfo $orderInfo,
        Timestamps $timestamps,
        MerchantData $merchantData,
        PaymentLinkCollection $paymentLinkCollection,
        bool $payerSetsAmount = false,
        ?int $minimumAmount = null,
        ?int $maximumAmount = null,
        bool $tipsEnabled = false,
        ?int $tipAmount = null
    ) {
        $this->id = $id;
        $this->orderInfo = $orderInfo;
        $this->timestamps = $timestamps;
        $this->merchantData = $merchantData;
        $this->paymentLinkCollection = $paymentLinkCollection;
        $this->payerSetsAmount = $payerSetsAmount;
        $this->minimumAmount = $minimumAmount;
        $this->maximumAmount = $maximumAmount;
        $this->tipsEnabled = $tipsEnabled;
        $this->tipAmount = $tipAmount;
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

    public function isTipsEnabled(): bool
    {
        return $this->tipsEnabled;
    }

    /**
     * Tip total of the paid payments, in the order currency. It is included in the order info amount paid,
     * never in the amount. It can be set while isTipsEnabled() is false, for tips through Split Payments.
     * It can be null while a payment carries a tip: on a payer-set order the tip is part of the entered
     * amount, and a tip in another currency or above the settled amount is not recorded on the order.
     */
    public function getTipAmount(): ?int
    {
        return $this->tipAmount;
    }

    /**
     * Amount paid without the tip, in minor units; this is what Paysera compares with the order amount.
     */
    public function getBasePaid(): int
    {
        return $this->orderInfo->getAmountPaid() - ($this->tipAmount ?? 0);
    }

    /**
     * True when the amount paid, tip excluded, covers the order amount.
     */
    public function isFullyPaid(): bool
    {
        return $this->getBasePaid() >= $this->orderInfo->getAmount();
    }
}
