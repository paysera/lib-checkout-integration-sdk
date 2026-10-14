<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Manager;

use Firebase\JWT\ExpiredException;
use Paysera\CheckoutSdk\Entity\PaymentApiAuthToken;
use Paysera\CheckoutSdk\Entity\PaymentApiJwtDecoded;
use Paysera\CheckoutSdk\Exception\JwtValidationException;
use Paysera\CheckoutSdk\Exception\PaymentApiAuthTokenException;
use Paysera\CheckoutSdk\Repository\PaymentApiAuthTokenRepositoryInterface;
use Paysera\CheckoutSdk\Service\JWT\PaymentApiJwtDecoderInterface;
use Psr\Clock\ClockInterface;
use Throwable;

class PaymentApiAuthTokenManager
{
    private const TOKEN_REFRESH_BUFFER_SECONDS = 300;

    private PaymentApiAuthTokenRepositoryInterface $authTokenRepository;
    private ClockInterface $clock;
    private PaymentApiJwtDecoderInterface $jwtDecoder;

    public function __construct(
        PaymentApiAuthTokenRepositoryInterface $authTokenRepository,
        ClockInterface $clock,
        PaymentApiJwtDecoderInterface $apiJwtDecoder
    ) {
        $this->authTokenRepository = $authTokenRepository;
        $this->clock = $clock;
        $this->jwtDecoder = $apiJwtDecoder;
    }

    /**
     * @throws PaymentApiAuthTokenException
     */
    public function saveToken(PaymentApiAuthToken $authToken): void
    {
        try {
            $this->authTokenRepository->save($authToken);
        } catch (Throwable $exception) {
            throw new PaymentApiAuthTokenException(
                'Failed to save auth token',
                null,
                $exception
            );
        }
    }

    /**
     * @throws PaymentApiAuthTokenException
     */
    public function getStoredToken(): PaymentApiAuthToken
    {
        try {
            $authToken = $this->authTokenRepository->retrieve();
        } catch (Throwable $exception) {
            throw new PaymentApiAuthTokenException(
                'Failed to retrieve auth token',
                null,
                $exception
            );
        }

        if ($authToken === null) {
            throw new PaymentApiAuthTokenException('No auth token found in storage');
        }

        return $authToken;
    }

    /**
     * @throws PaymentApiAuthTokenException
     */
    public function hasStoredToken(): bool
    {
        try {
            return $this->authTokenRepository->retrieve() !== null;
        } catch (Throwable $exception) {
            throw new PaymentApiAuthTokenException(
                'Failed to retrieve auth token',
                null,
                $exception
            );
        }
    }

    /**
     * @uses getJwtDecoded
     */
    public function isTokenFresh(PaymentApiAuthToken $authToken): bool
    {
        $currentTime = $this->clock
            ->now()
            ->getTimestamp()
        ;

        try {
            $decoded = $this->getJwtDecoded($authToken);
        } catch (ExpiredException|JwtValidationException $exception) {
            return false;
        }

        return $currentTime < ($decoded->getExpiresAt() - self::TOKEN_REFRESH_BUFFER_SECONDS);
    }

    /**
     * @throws ExpiredException
     * @throws JwtValidationException
     */
    public function getJwtDecoded(PaymentApiAuthToken $authToken): PaymentApiJwtDecoded
    {
        $decoded = $this->jwtDecoder->decode($authToken->getAccessToken());

        return new PaymentApiJwtDecoded($decoded);
    }
}
