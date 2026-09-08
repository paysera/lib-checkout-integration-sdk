<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Facade;

use Paysera\CheckoutSdk\Entity\PaymentApiCredentials;
use Paysera\CheckoutSdk\Entity\PaymentApiAuthToken;
use Paysera\CheckoutSdk\Entity\PaymentApiJwtDecoded;
use Paysera\CheckoutSdk\Exception\JwtValidationException;
use Paysera\CheckoutSdk\Exception\PaymentApiAuthTokenException;
use Paysera\CheckoutSdk\Exception\IntegrationException;
use Paysera\CheckoutSdk\Exception\PaymentApiCredentialsException;
use Paysera\CheckoutSdk\Service\Manager\PaymentApiAuthTokenManager;
use Paysera\CheckoutSdk\Service\Manager\PaymentApiCredentialsManager;
use Paysera\CheckoutSdk\Service\Provider\PaymentApiAuthTokenProvider;
use Psr\Log\LoggerInterface;

class Authorization
{
    private LoggerInterface $logger;
    private PaymentApiAuthTokenProvider $paymentApiAuthTokenProvider;
    private PaymentApiCredentialsManager $paymentApiCredentialsManager;
    private PaymentApiAuthTokenManager $paymentApiAuthTokenManager;

    public function __construct(
        LoggerInterface $logger,
        PaymentApiAuthTokenProvider $paymentApiAuthTokenProvider,
        PaymentApiCredentialsManager $paymentApiCredentialsManager,
        PaymentApiAuthTokenManager $paymentApiAuthTokenManager
    ) {
        $this->logger = $logger;
        $this->paymentApiAuthTokenProvider = $paymentApiAuthTokenProvider;
        $this->paymentApiCredentialsManager = $paymentApiCredentialsManager;
        $this->paymentApiAuthTokenManager = $paymentApiAuthTokenManager;
    }

    /**
     * @throws IntegrationException
     */
    public function authorize(PaymentApiCredentials $apiCredentials): PaymentApiAuthToken
    {
        try {
            return $this->paymentApiAuthTokenProvider->issueNewAuthToken($apiCredentials);
        } catch (PaymentApiAuthTokenException $exception) {
            $this->logger->error(
                'Authorization failed: unable to issue new payment API token',
                [
                    'exception' => $exception,
                ]
            );
            throw new IntegrationException(
                'Authorization failed: unable to issue new payment API token',
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws IntegrationException
     */
    public function reauthorize(): PaymentApiAuthToken
    {
        try {
            return $this->paymentApiAuthTokenProvider->refreshStoredAuthToken();
        } catch (PaymentApiAuthTokenException $exception) {
            $this->logger->error(
                'Authorization failed: unable to refresh stored payment API token',
                [
                    'exception' => $exception,
                ]
            );
            throw new IntegrationException(
                'Authorization failed: unable to refresh stored payment API token',
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws IntegrationException
     */
    public function getStoredPaymentApiCredentials(): ?PaymentApiCredentials
    {
        try {
            if (!$this->paymentApiCredentialsManager->hasStoredCredentials()) {
                return null;
            }

            return $this->paymentApiCredentialsManager->getStoredCredentials();
        } catch (PaymentApiCredentialsException $exception) {
            $this->logger->error(
                'Unable to provide stored payment API credentials',
                [
                    'exception' => $exception,
                ]
            );
            throw new IntegrationException(
                'Unable to provide stored payment API credentials',
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws IntegrationException
     */
    public function getStoredPaymentApiAuthToken(): ?PaymentApiAuthToken
    {
        try {
            if (!$this->paymentApiAuthTokenManager->hasStoredToken()) {
                return null;
            }

            return $this->paymentApiAuthTokenManager->getStoredToken();
        } catch (PaymentApiAuthTokenException $exception) {
            $this->logger->error(
                'Unable to provide stored payment API authorization token',
                [
                    'exception' => $exception,
                ]
            );
            throw new IntegrationException(
                'Unable to provide stored payment API authorization token',
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws IntegrationException
     */
    public function getDecodedToken(): ?PaymentApiJwtDecoded
    {
        try {
            if (!$this->paymentApiAuthTokenManager->hasStoredToken()) {
                return null;
            }

            $authToken = $this->paymentApiAuthTokenManager->getStoredToken();

            return $this->paymentApiAuthTokenManager->getJwtDecoded($authToken);
        } catch (JwtValidationException $exception) {
            $this->logger->error(
                'Unable to decode stored payment API token',
                [
                    'exception' => $exception,
                ]
            );
            throw new IntegrationException(
                'Unable to decode stored payment API token',
                $exception->getCode(),
                $exception
            );
        } catch (PaymentApiAuthTokenException $exception) {
            $this->logger->error(
                'Unable to decode stored payment API token',
                [
                    'exception' => $exception,
                ]
            );
            throw new IntegrationException(
                'Unable to decode stored payment API token',
                $exception->getCode(),
                $exception
            );
        }
    }
}
