<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\PaymentOrder;

class RedirectUrls
{
    private ?string $successUrl;
    private ?string $failureUrl;
    private ?string $callbackUrl;
    private ?string $cancelUrl;

    public function __construct(
        ?string $successUrl = null,
        ?string $failureUrl = null,
        ?string $callbackUrl = null,
        ?string $cancelUrl = null
    ) {
        $this->successUrl = $successUrl;
        $this->failureUrl = $failureUrl;
        $this->callbackUrl = $callbackUrl;
        $this->cancelUrl = $cancelUrl;
    }

    public function getSuccessUrl(): ?string
    {
        return $this->successUrl;
    }

    public function getFailureUrl(): ?string
    {
        return $this->failureUrl;
    }

    public function getCallbackUrl(): ?string
    {
        return $this->callbackUrl;
    }

    public function getCancelUrl(): ?string
    {
        return $this->cancelUrl;
    }
}
