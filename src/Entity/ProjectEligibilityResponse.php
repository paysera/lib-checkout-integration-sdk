<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

class ProjectEligibilityResponse
{
    private ProjectEligibilityStatus $status;
    private ?bool $paymentCollectionEnabled;
    private ?bool $urlVerified;
    private ?bool $testMode;

    public function __construct(
        ProjectEligibilityStatus $status,
        ?bool $paymentCollectionEnabled,
        ?bool $urlVerified,
        ?bool $testMode = false
    ) {
        $this->status = $status;
        $this->paymentCollectionEnabled = $paymentCollectionEnabled;
        $this->urlVerified = $urlVerified;
        $this->testMode = $testMode;
    }

    public function isEligibleForPayments(): bool
    {
        return $this->status->getValue() === ProjectEligibilityStatus::ELIGIBLE;
    }

    public function getStatus(): ProjectEligibilityStatus
    {
        return $this->status;
    }

    public function getPaymentCollectionEnabled(): ?bool
    {
        return $this->paymentCollectionEnabled;
    }

    public function getUrlVerified(): ?bool
    {
        return $this->urlVerified;
    }

    public function isTestMode(): ?bool
    {
        return $this->testMode;
    }

    /**
     * @return array<string, mixed>
     */
    public function getLoggerData(): array
    {
        return [
            'status' => $this->status->getValue(),
            'payment_collection_enabled' => $this->paymentCollectionEnabled,
            'url_verified' => $this->urlVerified,
            'test_mode' => $this->testMode,
        ];
    }
}
