<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use Paysera\CheckoutSdk\Entity\PaymentOrder\Purchase;
use Paysera\CheckoutSdk\Entity\PaymentOrder\RedirectUrls;

class PaymentOrderCreateRequest
{
    private Purchase $purchase;
    private ?RedirectUrls $redirectUrls;
    private Metadata $metadata;
    private ?string $source;

    public function __construct(
        Purchase $purchase,
        Metadata $metadata,
        ?RedirectUrls $redirectUrls = null,
        ?string $source = null
    ) {
        $this->purchase = $purchase;
        $this->redirectUrls = $redirectUrls;
        $this->metadata = $metadata;
        $this->source = $source;
    }

    public function getPurchase(): Purchase
    {
        return $this->purchase;
    }

    public function getRedirectUrls(): ?RedirectUrls
    {
        return $this->redirectUrls;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function getLoggerData(): array
    {
        return [
            'reference' => $this->getPurchase()->getReference(),
            'amount' => $this->getPurchase()->getAmount(),
            'currency' => $this->getPurchase()->getCurrency(),
            'payer_sets_amount' => $this->getPurchase()->isPayerSetsAmount(),
            'suggested_amount' => $this->getPurchase()->getSuggestedAmount(),
            'amount_buttons' => $this->getPurchase()->getAmountButtons(),
            'source' => $this->getSource(),
        ];
    }
}
