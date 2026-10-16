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
    private bool $tipsEnabled;

    public function __construct(
        Purchase $purchase,
        Metadata $metadata,
        ?RedirectUrls $redirectUrls = null,
        ?string $source = null,
        bool $tipsEnabled = false
    ) {
        $this->purchase = $purchase;
        $this->redirectUrls = $redirectUrls;
        $this->metadata = $metadata;
        $this->source = $source;
        $this->tipsEnabled = $tipsEnabled;
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

    /**
     * True when the payer is offered a tip on top of the fixed amount, outside Split Payments.
     */
    public function isTipsEnabled(): bool
    {
        return $this->tipsEnabled;
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
            'tips_enabled' => $this->isTipsEnabled(),
            'source' => $this->getSource(),
        ];
    }
}
