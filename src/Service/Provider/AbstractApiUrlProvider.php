<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Provider;

use Paysera\CheckoutSdk\Exception\RuntimeException;
use Paysera\CheckoutSdk\Service\PaymentApiConfiguration;
use Psr\Log\LoggerInterface;

abstract class AbstractApiUrlProvider
{
    public const PRODUCTION_BASE_URL = '';
    public const SANDBOX_BASE_URL = '';

    protected LoggerInterface $logger;
    private PaymentApiConfiguration $paymentApiConfiguration;
    private string $productionBaseUrl;
    private string $sandboxBaseUrl;

    public function __construct(
        PaymentApiConfiguration $paymentApiConfiguration,
        LoggerInterface $logger,
        ?string $productionBaseUrl = null,
        ?string $sandboxBaseUrl = null
    ) {
        $this->logger = $logger;
        $this->paymentApiConfiguration = $paymentApiConfiguration;
        $this->productionBaseUrl = $productionBaseUrl ?? static::PRODUCTION_BASE_URL;
        $this->sandboxBaseUrl = $sandboxBaseUrl ?? static::SANDBOX_BASE_URL;

        if ($productionBaseUrl !== null || $sandboxBaseUrl !== null) {
            $logger
                ->warning(
                    $this->getBaseUrlOverrideWarning(),
                    [
                        'production_url' => $productionBaseUrl ?? 'default',
                        'sandbox_url' => $sandboxBaseUrl ?? 'default',
                    ]
                )
            ;
        }
    }

    protected function isSandboxEnvironment(): bool
    {
        return $this->paymentApiConfiguration
            ->getPaymentApiEnvironment()
            ->isSandbox()
        ;
    }

    /**
     * @param array<string, string> $replacements
     *
     * @throws RuntimeException
     */
    protected function buildUrl(string $endpoint, array $replacements = []): string
    {
        foreach ($replacements as $key => $value) {
            if (trim($value) === '') {
                throw new RuntimeException('URL placeholder value cannot be empty ' . $key);
            }

            $endpoint = str_replace('{' . $key . '}', $this->encodePlaceholderValue($value), $endpoint);
        }

        return rtrim($this->getBaseUrl(), '/') . '/' . ltrim($endpoint, '/');
    }

    protected function getBaseUrl(): string
    {
        return $this->isSandboxEnvironment() ? $this->sandboxBaseUrl : $this->productionBaseUrl;
    }

    /**
     * Hook for subclasses that must escape placeholder values (e.g. path
     * segments built from user-controlled identifiers). The default keeps the
     * value as-is, matching API endpoints whose placeholders are fixed.
     */
    protected function encodePlaceholderValue(string $value): string
    {
        return $value;
    }

    abstract protected function getBaseUrlOverrideWarning(): string;
}
