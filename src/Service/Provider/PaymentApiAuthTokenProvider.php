<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Provider;

use Paysera\CheckoutSdk\Entity\PaymentApiAuthToken;
use Paysera\CheckoutSdk\Entity\PaymentApiCredentials;
use Paysera\CheckoutSdk\Exception\BaseException;
use Paysera\CheckoutSdk\Exception\PaymentApiAuthTokenException;
use Paysera\CheckoutSdk\Exception\PaymentApiCredentialsException;
use Paysera\CheckoutSdk\Service\Client\PaymentApiAuthorizationClient;
use Paysera\CheckoutSdk\Service\Manager\PaymentApiAuthTokenManager;
use Paysera\CheckoutSdk\Service\Manager\PaymentApiCredentialsManager;

class PaymentApiAuthTokenProvider
{
    private PaymentApiAuthorizationClient $paymentApiAuthorizationClient;
    private PaymentApiAuthTokenManager $authTokenManager;
    private PaymentApiCredentialsManager $paymentApiCredentialsManager;

    public function __construct(
        PaymentApiAuthorizationClient $paymentApiAuthorizationClient,
        PaymentApiAuthTokenManager $authTokenManager,
        PaymentApiCredentialsManager $paymentApiCredentialsManager
    ) {
        $this->paymentApiAuthorizationClient = $paymentApiAuthorizationClient;
        $this->authTokenManager = $authTokenManager;
        $this->paymentApiCredentialsManager = $paymentApiCredentialsManager;
    }

    /**
     * @throws PaymentApiAuthTokenException
     */
    public function issueNewAuthToken(PaymentApiCredentials $apiCredentials): PaymentApiAuthToken
    {
        try {
            $authToken = $this->paymentApiAuthorizationClient->getAuthToken($apiCredentials);
            $this->authTokenManager->saveToken($authToken);
            $this->paymentApiCredentialsManager->saveCredentials($apiCredentials);

            return $authToken;
        } catch (BaseException $exception) {
            throw new PaymentApiAuthTokenException(
                'Unable to issue new payment API auth token',
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws PaymentApiAuthTokenException
     */
    public function refreshStoredAuthToken(): PaymentApiAuthToken
    {
        try {
            $apiCredentials = $this->paymentApiCredentialsManager->getStoredCredentials();
            $authToken = $this->paymentApiAuthorizationClient->getAuthToken($apiCredentials);
            $this->authTokenManager->saveToken($authToken);

            return $authToken;
        } catch (BaseException $exception) {
            throw new PaymentApiAuthTokenException(
                'Unable to refresh stored payment API auth token',
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws PaymentApiAuthTokenException
     * @throws PaymentApiCredentialsException
     */
    public function getAuthToken(): PaymentApiAuthToken
    {
        if (!$this->authTokenManager->hasStoredToken() && $this->paymentApiCredentialsManager->hasStoredCredentials()) {
            return $this->issueNewAuthToken($this->paymentApiCredentialsManager->getStoredCredentials());
        }

        $authToken = $this->authTokenManager->getStoredToken();

        if ($this->authTokenManager->isTokenFresh($authToken)) {
            return $authToken;
        }

        return $this->refreshStoredAuthToken();
    }
}
