<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Facade;

use Paysera\CheckoutSdk\Exception\IntegrationException;
use Paysera\CheckoutSdk\Service\Provider\MerchantAreaUrlProvider;
use Psr\Log\LoggerInterface;

class MerchantArea
{
    private LoggerInterface $logger;
    private Authorization $authorization;
    private MerchantAreaUrlProvider $merchantAreaUrlProvider;

    public function __construct(
        LoggerInterface $logger,
        Authorization $authorization,
        MerchantAreaUrlProvider $merchantAreaUrlProvider
    ) {
        $this->logger = $logger;
        $this->authorization = $authorization;
        $this->merchantAreaUrlProvider = $merchantAreaUrlProvider;
    }

    /**
     * Project-specific Merchant Area root URL, or null when the project id
     * cannot be resolved (no stored token / decode failure).
     */
    public function getRootUrl(): ?string
    {
        $projectId = $this->resolveProjectId();
        if ($projectId === null) {
            return null;
        }

        return $this->merchantAreaUrlProvider->getRootUrl($projectId);
    }

    /**
     * Project-specific Merchant Area integration credentials URL, or null when
     * the project id cannot be resolved (no stored token / decode failure).
     */
    public function getCredentialsUrl(): ?string
    {
        $projectId = $this->resolveProjectId();
        if ($projectId === null) {
            return null;
        }

        return $this->merchantAreaUrlProvider->getCredentialsUrl($projectId);
    }

    /**
     * Project-specific Merchant Area website verification URL, or null when the
     * project id cannot be resolved (no stored token / decode failure).
     */
    public function getWebsitesValidationUrl(): ?string
    {
        $projectId = $this->resolveProjectId();
        if ($projectId === null) {
            return null;
        }

        return $this->merchantAreaUrlProvider->getWebsitesValidationUrl($projectId);
    }

    /**
     * Merchant Area project list URL. Unlike the other URLs it is never null:
     * it needs no project id, so plugins can show it before authorization to
     * guide the merchant to their API credentials
     * (project > Integrations > API credentials).
     */
    public function getProjectListUrl(): string
    {
        return $this->merchantAreaUrlProvider->getProjectListUrl();
    }

    private function resolveProjectId(): ?string
    {
        try {
            $decodedToken = $this->authorization->getDecodedToken();
        } catch (IntegrationException $exception) {
            $this->logger->error(
                'Unable to resolve project id for Merchant Area URL',
                [
                    'exception' => $exception,
                ]
            );

            return null;
        }

        if ($decodedToken === null) {
            return null;
        }

        $projectId = $decodedToken->getProjectId();
        if (trim($projectId) === '') {
            return null;
        }

        return $projectId;
    }
}
