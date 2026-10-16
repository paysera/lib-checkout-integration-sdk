<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Provider;

use Paysera\CheckoutSdk\Exception\RuntimeException;

class MerchantAreaUrlProvider extends AbstractApiUrlProvider
{
    public const PRODUCTION_BASE_URL = 'https://bank.paysera.com';

    /**
     * Assumed by analogy with the Payment API sandbox host. There is no
     * dedicated sandbox Merchant Area yet, so this default is a guess until the
     * sandbox feature is delivered. Overridable via
     * PAYSERA_CHECKOUT_SDK_MERCHANT_AREA_SANDBOX_BASE_URL.
     *
     * @todo Confirm the real sandbox Merchant Area host once it is delivered.
     */
    public const SANDBOX_BASE_URL = 'https://sandbox.bank.paysera.com';

    public const ROOT_PATH = '/shell/checkout/{project_id}/overview';
    public const CREDENTIALS_PATH = '/shell/checkout/{project_id}/integrations';
    public const WEBSITES_PATH = '/shell/checkout/{project_id}/settings/websites';

    /**
     * Builds the project-specific Merchant Area root URL.
     *
     * @throws RuntimeException when the project id is empty
     */
    public function getRootUrl(string $projectId): string
    {
        return $this->buildUrl(static::ROOT_PATH, ['project_id' => $projectId]);
    }

    /**
     * Builds the project-specific Merchant Area integration credentials URL.
     *
     * @throws RuntimeException when the project id is empty
     */
    public function getCredentialsUrl(string $projectId): string
    {
        return $this->buildUrl(static::CREDENTIALS_PATH, ['project_id' => $projectId]);
    }

    /**
     * Builds the project-specific Merchant Area website verification URL, where
     * the merchant adds and verifies the store URLs of the project.
     *
     * @throws RuntimeException when the project id is empty
     */
    public function getWebsitesValidationUrl(string $projectId): string
    {
        return $this->buildUrl(static::WEBSITES_PATH, ['project_id' => $projectId]);
    }

    protected function encodePlaceholderValue(string $value): string
    {
        return rawurlencode($value);
    }

    protected function getBaseUrlOverrideWarning(): string
    {
        return 'Merchant Area base URL environment override detected';
    }
}
