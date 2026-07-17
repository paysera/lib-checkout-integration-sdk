<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service;

use Paysera\CheckoutSdk\Entity\PaymentApiCredentials;
use Paysera\CheckoutSdk\Exception\BaseException;
use Paysera\CheckoutSdk\Exception\RuntimeException;
use Paysera\CheckoutSdk\Exception\VerificationException;
use Paysera\CheckoutSdk\Repository\PaymentApiCredentialsRepositoryInterface;
use Paysera\CheckoutSdk\Util\MessagePayloadExtractor;
use Psr\Http\Message\RequestInterface;

class PaymentApiCallbackVerifier
{
    public const HASH_ALGO = 'sha256';

    private MessagePayloadExtractor $messagePayloadExtractor;
    private PaymentApiCallbackHeadersBuilder $paymentApiCallbackHeadersBuilder;
    private PaymentApiCredentialsRepositoryInterface $paymentApiCredentialsRepository;

    public function __construct(
        MessagePayloadExtractor $messagePayloadExtractor,
        PaymentApiCallbackHeadersBuilder $paymentApiCallbackHeadersBuilder,
        PaymentApiCredentialsRepositoryInterface $paymentApiCredentialsRepository
    ) {
        $this->messagePayloadExtractor = $messagePayloadExtractor;
        $this->paymentApiCallbackHeadersBuilder = $paymentApiCallbackHeadersBuilder;
        $this->paymentApiCredentialsRepository = $paymentApiCredentialsRepository;
    }

    /**
     * @throws VerificationException
     * @throws RuntimeException
     * @throws BaseException
     */
    public function verify(RequestInterface $request): void
    {
        if (!in_array(self::HASH_ALGO, hash_hmac_algos(), true)) {
            throw new RuntimeException('Selected hash algorithm is not available'); // @codeCoverageIgnore
        }

        $paymentApiCallbackHeaders = $this->paymentApiCallbackHeadersBuilder
            ->buildPaymentCallbackHeaders($request)
        ;
        $paymentApiCredentials = $this->getPaymentApiCredentials();

        $calculatedSignature = hash_hmac(
            self::HASH_ALGO,
            $this->messagePayloadExtractor->getPayload($request),
            $paymentApiCredentials->getClientSecret()
        );

        if (!hash_equals($calculatedSignature, $paymentApiCallbackHeaders->getSignature())) {
            throw new VerificationException('Callback request signature does not match');
        }
    }

    /**
     * @throws RuntimeException
     */
    private function getPaymentApiCredentials(): PaymentApiCredentials
    {
        $paymentApiCredentials = $this->paymentApiCredentialsRepository->retrieve();

        if ($paymentApiCredentials === null) {
            throw new RuntimeException('Payment API credentials not found');
        }

        return $paymentApiCredentials;
    }
}
