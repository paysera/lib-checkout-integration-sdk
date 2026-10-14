<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use DateTimeImmutable;
use Paysera\CheckoutSdk\Entity\PaymentLink\Experience;
use Paysera\CheckoutSdk\Entity\PaymentLink\PaymentDetails;
use Paysera\CheckoutSdk\Entity\PaymentLink\Purchase;
use Paysera\CheckoutSdk\Entity\PaymentLink\PayerInformation;
use Paysera\CheckoutSdk\Entity\Metadata;

class PaymentLinkCreateResponse
{
    private string $linkId;
    private string $orderId;
    private string $paymentUrl;
    private Experience $experience;
    private ?PaymentDetails $paymentDetails;
    private Purchase $purchase;
    private ?PayerInformation $payerInformation;
    private ?DateTimeImmutable $expiredAt;
    private DateTimeImmutable $createdAt;
    private Metadata $metadata;

    public function __construct(
        string $linkId,
        string $orderId,
        string $paymentUrl,
        Experience $experience,
        Purchase $purchase,
        DateTimeImmutable $createdAt,
        Metadata $metadata,
        ?PaymentDetails $paymentDetails = null,
        ?PayerInformation $payerInformation = null,
        ?DateTimeImmutable $expiredAt = null
    ) {
        $this->linkId = $linkId;
        $this->orderId = $orderId;
        $this->paymentUrl = $paymentUrl;
        $this->experience = $experience;
        $this->paymentDetails = $paymentDetails;
        $this->purchase = $purchase;
        $this->payerInformation = $payerInformation;
        $this->expiredAt = $expiredAt;
        $this->createdAt = $createdAt;
        $this->metadata = $metadata;
    }

    public function getLinkId(): string
    {
        return $this->linkId;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getPaymentUrl(): string
    {
        return $this->paymentUrl;
    }

    public function getExperience(): Experience
    {
        return $this->experience;
    }

    public function getPaymentDetails(): ?PaymentDetails
    {
        return $this->paymentDetails;
    }

    public function getPurchase(): Purchase
    {
        return $this->purchase;
    }

    public function getPayerInformation(): ?PayerInformation
    {
        return $this->payerInformation;
    }

    public function getExpiredAt(): ?DateTimeImmutable
    {
        return $this->expiredAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function getLoggerData(): array
    {
        return [
            'link_id' => $this->getLinkId(),
            'order_id' => $this->getOrderId(),
            'payment_url' => $this->getPaymentUrl(),
            'amount' => $this->getPurchase()->getAmount(),
            'payer_sets_amount' => $this->getPurchase()->isPayerSetsAmount(),
            'suggested_amount' => $this->getPurchase()->getSuggestedAmount(),
            'amount_buttons' => $this->getPurchase()->getAmountButtons(),
            'language' => $this->getExperience()->getLanguage(),
            'created_at' => $this->getCreatedAt()->getTimestamp(),
        ];
    }
}
