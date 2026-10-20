<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

class ProjectEligibilityCheckRequest
{
    private string $storeUrl;

    public function __construct(string $storeUrl)
    {
        $this->storeUrl = $storeUrl;
    }

    public function getStoreUrl(): string
    {
        return $this->storeUrl;
    }

    /**
     * @return array<string, string>
     */
    public function getLoggerData(): array
    {
        return [
            'store_url' => $this->storeUrl,
        ];
    }
}
