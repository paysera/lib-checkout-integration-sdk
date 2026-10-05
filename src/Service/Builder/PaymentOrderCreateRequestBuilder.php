<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Builder;

use Paysera\CheckoutSdk\Entity\Metadata;
use Paysera\CheckoutSdk\Entity\PaymentOrder\Purchase;
use Paysera\CheckoutSdk\Entity\PaymentOrder\RedirectUrls;
use Paysera\CheckoutSdk\Entity\PaymentOrderCreateRequest;
use Paysera\CheckoutSdk\Entity\PaymentOrderSource;
use Paysera\CheckoutSdk\Exception\ValidationException;

class PaymentOrderCreateRequestBuilder
{
    private ?Purchase $purchase = null;
    private ?Metadata $metadata = null;
    private ?RedirectUrls $redirectUrls = null;
    private ?string $source = null;

    public function setPurchase(Purchase $purchase): self
    {
        $this->purchase = $purchase;

        return $this;
    }

    public function setMetadata(Metadata $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function setRedirectUrls(?RedirectUrls $redirectUrls): self
    {
        $this->redirectUrls = $redirectUrls;

        return $this;
    }

    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @throws ValidationException
     */
    public function build(): PaymentOrderCreateRequest
    {
        if ($this->purchase === null) {
            throw (new ValidationException())
                ->setContext(['purchase' => 'Purchase is required to build a payment order create request.'])
            ;
        }

        if ($this->metadata === null) {
            throw (new ValidationException())
                ->setContext(['metadata' => 'Metadata is required to build a payment order create request.'])
            ;
        }

        if ($this->source !== null && !in_array($this->source, PaymentOrderSource::SOURCES, true)) {
            throw (new ValidationException())
                ->setContext([
                    'source' => sprintf('Payment order source "%s" is not supported.', $this->source),
                ])
            ;
        }

        return new PaymentOrderCreateRequest(
            $this->purchase,
            $this->metadata,
            $this->redirectUrls,
            $this->source
        );
    }
}
