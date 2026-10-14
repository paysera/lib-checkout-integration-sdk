<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Provider;

use Paysera\CheckoutSdk\Exception\RuntimeException;

class PaymentApiUrlProvider extends AbstractApiUrlProvider
{
    public const PRODUCTION_BASE_URL = 'https://api.paysera.com';
    public const SANDBOX_BASE_URL = 'https://sandbox.paysera.com';

    public const AUTH_PATH = '/auth/realms/Paysera/protocol/openid-connect/token';
    public const JWKS_PATH = '/auth/realms/Paysera/protocol/openid-connect/certs';

    public const PAYMENT_METHODS_PATH = '/checkout-project/integration/v1/methods';
    public const REFUND_REQUEST_PATH = '/v1/refund/refund_requests';
    public const PAYMENT_ORDER_CREATE_PATH = '/merchant-order/integration/v1/orders';
    public const PAYMENT_LINK_CREATE_PATH = '/checkout-payment-link/integration/v1/payment-links';
    public const PROJECT_INFO_PATH = '/checkout-project/integration/v1/project';
    public const PROJECT_WEBSITES_PATH = '/checkout-project/integration/v1/websites';

    /**
     * @throws RuntimeException
     */
    public function getAuthUrl(): string
    {
        return $this->buildUrl(self::AUTH_PATH);
    }

    /**
     * @throws RuntimeException
     */
    public function getJwksUrl(): string
    {
        return $this->buildUrl(static::JWKS_PATH);
    }

    /**
     * @throws RuntimeException
     */
    public function getPaymentMethodsUrl(): string
    {
        return $this->buildUrl(static::PAYMENT_METHODS_PATH);
    }

    /**
     * @throws RuntimeException
     */
    public function getInitiateRefundOrderUrl(): string
    {
        return $this->buildUrl(static::REFUND_REQUEST_PATH);
    }

    /**
     * @throws RuntimeException
     */
    public function getCreatePaymentOrderUrl(): string
    {
        return $this->buildUrl(static::PAYMENT_ORDER_CREATE_PATH);
    }

    /**
     * @throws RuntimeException
     */
    public function getCreatePaymentLinkUrl(): string
    {
        return $this->buildUrl(static::PAYMENT_LINK_CREATE_PATH);
    }

    /**
     * @throws RuntimeException
     */
    public function getProjectInfoUrl(): string
    {
        return $this->buildUrl(static::PROJECT_INFO_PATH);
    }

    /**
     * @throws RuntimeException
     */
    public function getProjectWebsitesUrl(): string
    {
        return $this->buildUrl(static::PROJECT_WEBSITES_PATH);
    }

    protected function getBaseUrlOverrideWarning(): string
    {
        return 'Payment API base URL environment override detected';
    }
}
