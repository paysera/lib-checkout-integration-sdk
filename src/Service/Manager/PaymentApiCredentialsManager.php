<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Manager;

use Paysera\CheckoutSdk\Entity\PaymentApiCredentials;
use Paysera\CheckoutSdk\Exception\PaymentApiCredentialsException;
use Paysera\CheckoutSdk\Repository\PaymentApiCredentialsRepositoryInterface;
use Throwable;

class PaymentApiCredentialsManager
{
    private PaymentApiCredentialsRepositoryInterface $apiCredentialsRepository;

    public function __construct(PaymentApiCredentialsRepositoryInterface $apiCredentialsRepository)
    {
        $this->apiCredentialsRepository = $apiCredentialsRepository;
    }

    /**
     * @throws PaymentApiCredentialsException
     */
    public function saveCredentials(PaymentApiCredentials $apiCredentials): void
    {
        try {
            $this->apiCredentialsRepository->save($apiCredentials);
        } catch (Throwable $exception) {
            throw new PaymentApiCredentialsException(
                'Failed to save API credentials',
                null,
                $exception
            );
        }
    }

    /**
     * @throws PaymentApiCredentialsException
     */
    public function getStoredCredentials(): PaymentApiCredentials
    {
        try {
            $apiCredentials = $this->apiCredentialsRepository->retrieve();
        } catch (Throwable $exception) {
            throw new PaymentApiCredentialsException(
                'Failed to retrieve API credentials',
                null,
                $exception
            );
        }

        if ($apiCredentials === null) {
            throw new PaymentApiCredentialsException('No API credentials found in storage');
        }

        return $apiCredentials;
    }

    /**
     * @throws PaymentApiCredentialsException
     */
    public function hasStoredCredentials(): bool
    {
        try {
            return $this->apiCredentialsRepository->retrieve() !== null;
        } catch (Throwable $exception) {
            throw new PaymentApiCredentialsException(
                'Failed to retrieve API credentials',
                null,
                $exception
            );
        }
    }
}
