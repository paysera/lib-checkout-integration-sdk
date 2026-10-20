<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\RefundOrder;

class RefundUrls
{
    private string $successUrl;
    private string $failureUrl;
    private string $callbackUrl;

    public function __construct(
        string $successUrl,
        string $failureUrl,
        string $callbackUrl
    ) {
        $this->successUrl = $successUrl;
        $this->failureUrl = $failureUrl;
        $this->callbackUrl = $callbackUrl;
    }

    public function getSuccessUrl(): string
    {
        return $this->successUrl;
    }

    public function getFailureUrl(): string
    {
        return $this->failureUrl;
    }

    public function getCallbackUrl(): string
    {
        return $this->callbackUrl;
    }
}
