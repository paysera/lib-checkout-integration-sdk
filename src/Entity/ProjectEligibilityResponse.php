<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use Paysera\CheckoutSdk\Entity\Collection\ProjectEligibilityReasonCollection;

class ProjectEligibilityResponse
{
    private ProjectEligibilityStatus $status;
    private ?bool $paymentCollectionEnabled;
    private ?bool $urlVerified;
    private ?bool $testMode;
    private ProjectEligibilityReasonCollection $reasons;
    private ?ProjectStatus $projectStatus;

    public function __construct(
        ProjectEligibilityStatus $status,
        ?bool $paymentCollectionEnabled,
        ?bool $urlVerified,
        ?bool $testMode = false,
        ?ProjectEligibilityReasonCollection $reasons = null,
        ?ProjectStatus $projectStatus = null
    ) {
        $this->status = $status;
        $this->paymentCollectionEnabled = $paymentCollectionEnabled;
        $this->urlVerified = $urlVerified;
        $this->testMode = $testMode;
        $this->reasons = $reasons ?? new ProjectEligibilityReasonCollection();
        $this->projectStatus = $projectStatus;
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
     * Blocker categories behind an ineligible verdict. Empty for every other status.
     */
    public function getReasons(): ProjectEligibilityReasonCollection
    {
        return $this->reasons;
    }

    public function getProjectStatus(): ?ProjectStatus
    {
        return $this->projectStatus;
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
            'reasons' => $this->reasons->getValues(),
            'project_status' => $this->projectStatus !== null ? $this->projectStatus->getValue() : null,
        ];
    }
}
