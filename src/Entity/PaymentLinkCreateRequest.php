<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use Paysera\CheckoutSdk\Entity\PaymentLink\Experience;
use Paysera\CheckoutSdk\Entity\PaymentLink\PaymentDetails;
use Paysera\CheckoutSdk\Entity\PaymentLink\Purchase;
use Paysera\CheckoutSdk\Entity\PaymentLink\PayerInformation;
use Paysera\CheckoutSdk\Entity\Metadata;

class PaymentLinkCreateRequest
{
    private string $name;
    private ?int $lifetime;
    private Experience $experience;
    private ?PaymentDetails $paymentDetails;
    private Purchase $purchase;
    private ?PayerInformation $payerInformation;
    private ?string $orderId;
    private Metadata $metadata;

    public function __construct(
        string $name,
        Experience $experience,
        Purchase $purchase,
        Metadata $metadata,
        ?string $orderId = null,
        ?int $lifetime = null,
        ?PaymentDetails $paymentDetails = null,
        ?PayerInformation $payerInformation = null
    ) {
        $this->name = $name;
        $this->lifetime = $lifetime;
        $this->experience = $experience;
        $this->paymentDetails = $paymentDetails;
        $this->purchase = $purchase;
        $this->payerInformation = $payerInformation;
        $this->orderId = $orderId;
        $this->metadata = $metadata;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLifetime(): ?int
    {
        return $this->lifetime;
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

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(?string $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function getLoggerData(): array
    {
        return [
            'name' => $this->getName(),
            'order_id' => $this->getOrderId(),
            'amount' => $this->getPurchase()->getAmount(),
            'suggested_amount' => $this->getPurchase()->getSuggestedAmount(),
            'amount_buttons' => $this->getPurchase()->getAmountButtons(),
            'language' => $this->getExperience()->getLanguage(),
            'lifetime' => $this->getLifetime(),
        ];
    }
}
