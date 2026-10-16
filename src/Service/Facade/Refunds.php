<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Facade;

use Paysera\CheckoutSdk\Entity\Collection\RefundStatusCollection;
use Paysera\CheckoutSdk\Entity\RefundOrderRequest;
use Paysera\CheckoutSdk\Entity\RefundOrderResponse;
use Paysera\CheckoutSdk\Exception\BaseException;
use Paysera\CheckoutSdk\Exception\IntegrationException;
use Paysera\CheckoutSdk\Exception\ValidationException;
use Paysera\CheckoutSdk\Service\Client\PaymentApiClient;
use Paysera\CheckoutSdk\Service\Normalizer\RefundOrderRequestNormalizer;
use Paysera\CheckoutSdk\Service\Provider\RefundStatusProvider;
use Paysera\CheckoutSdk\Service\Validator\RefundStatusValidator;
use Psr\Log\LoggerInterface;

class Refunds
{
    private LoggerInterface $logger;
    private PaymentApiClient $paymentApiClient;
    private RefundOrderRequestNormalizer $refundOrderRequestNormalizer;
    private RefundStatusValidator $refundStatusValidator;
    private RefundStatusProvider $refundStatusProvider;

    public function __construct(
        LoggerInterface $logger,
        PaymentApiClient $paymentApiClient,
        RefundOrderRequestNormalizer $refundOrderRequestNormalizer,
        RefundStatusValidator $refundStatusValidator,
        RefundStatusProvider $refundStatusProvider
    ) {
        $this->logger = $logger;
        $this->paymentApiClient = $paymentApiClient;
        $this->refundOrderRequestNormalizer = $refundOrderRequestNormalizer;
        $this->refundStatusValidator = $refundStatusValidator;
        $this->refundStatusProvider = $refundStatusProvider;
    }

    public function buildRefundOrderRequest(array $refundOrderData): RefundOrderRequest
    {
        return $this->refundOrderRequestNormalizer->denormalize($refundOrderData);
    }

    /**
     * @throws IntegrationException
     */
    public function initiateRefundOrder(RefundOrderRequest $refundOrderRequest): RefundOrderResponse
    {
        try {
            $this->logger->info(
                'Starting refund order.',
                $refundOrderRequest->getLoggerData()
            );

            $paymentOrderResponse = $this->paymentApiClient->initiateRefundOrder($refundOrderRequest);

            $this->logger->info(
                'Refund order completed successfully.',
                $paymentOrderResponse->getLoggerData()
            );

            return $paymentOrderResponse;
        } catch (BaseException $exception) {
            $this->logger->error(
                'Refund order failed',
                [
                    'exception' => $exception,
                    'refundOrderRequestData' => $refundOrderRequest->getLoggerData(),
                ]
            );

            throw new IntegrationException(
                'Refund order failed',
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function isRefundStatusValid(string $refundStatus): bool
    {
        try {
            $this->refundStatusValidator->validate($refundStatus);

            return true;
        } catch (ValidationException $exception) {
            $this->logger->warning(
                'Refund status is invalid',
                [
                    'exception' => $exception,
                ]
            );

            return false;
        }
    }

    public function getRefundStatuses(): RefundStatusCollection
    {
        return $this->refundStatusProvider->getRefundStatusCollection();
    }
}
