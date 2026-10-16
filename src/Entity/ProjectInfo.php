<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

class ProjectInfo
{
    private string $projectId;
    private ProjectStatus $status;
    private bool $paymentCollectionEnabled;
    private bool $testMode;

    public function __construct(
        string $projectId,
        ProjectStatus $status,
        bool $paymentCollectionEnabled,
        bool $testMode = false
    ) {
        $this->projectId = $projectId;
        $this->status = $status;
        $this->paymentCollectionEnabled = $paymentCollectionEnabled;
        $this->testMode = $testMode;
    }

    public function getProjectId(): string
    {
        return $this->projectId;
    }

    public function getStatus(): ProjectStatus
    {
        return $this->status;
    }

    public function isPaymentCollectionEnabled(): bool
    {
        return $this->paymentCollectionEnabled;
    }

    public function isTestMode(): bool
    {
        return $this->testMode;
    }
}
