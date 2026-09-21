<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Facade;

use Paysera\CheckoutSdk\Entity\Collection\PaymentMethodCollection;
use Paysera\CheckoutSdk\Entity\Collection\PaymentStatusCollection;
use Paysera\CheckoutSdk\Entity\PaymentApiEnvironment;
use Paysera\CheckoutSdk\Entity\PaymentLinkCreateRequest;
use Paysera\CheckoutSdk\Entity\PaymentLinkCreateResponse;
use Paysera\CheckoutSdk\Entity\PaymentMethodFilter;
use Paysera\CheckoutSdk\Entity\Metadata;
use Paysera\CheckoutSdk\Entity\PaymentOrder\Purchase;
use Paysera\CheckoutSdk\Entity\PaymentOrder\RedirectUrls;
use Paysera\CheckoutSdk\Entity\PaymentOrderCreateRequest;
use Paysera\CheckoutSdk\Entity\PaymentOrderCreateResponse;
use Paysera\CheckoutSdk\Exception\ApiClientException;
use Paysera\CheckoutSdk\Exception\BaseException;
use Paysera\CheckoutSdk\Exception\IntegrationException;
use Paysera\CheckoutSdk\Exception\ValidationException;
use Paysera\CheckoutSdk\Service\Builder\PaymentOrderCreateRequestBuilder;
use Paysera\CheckoutSdk\Service\Client\PaymentApiClient;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentLinkCreateRequestNormalizer;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentOrderCreateRequestNormalizer;
use Paysera\CheckoutSdk\Service\PaymentApiConfiguration;
use Paysera\CheckoutSdk\Service\Provider\PaymentStatusProvider;
use Paysera\CheckoutSdk\Service\Validator\PaymentStatusValidator;
use Psr\Log\LoggerInterface;

class Payments
{
    private LoggerInterface $logger;
    private PaymentStatusValidator $paymentStatusValidator;
    private PaymentStatusProvider $paymentStatusProvider;
    private PaymentApiClient $paymentApiClient;
    private PaymentApiConfiguration $paymentApiConfiguration;
    private PaymentOrderCreateRequestNormalizer $paymentOrderCreateRequestNormalizer;
    private PaymentLinkCreateRequestNormalizer $paymentLinkCreateRequestNormalizer;

    public function __construct(
        LoggerInterface $logger,
        PaymentStatusValidator $paymentStatusValidator,
        PaymentStatusProvider $paymentStatusProvider,
        PaymentApiClient $paymentApiClient,
        PaymentApiConfiguration $paymentApiConfiguration,
        PaymentOrderCreateRequestNormalizer $paymentOrderCreateRequestNormalizer,
        PaymentLinkCreateRequestNormalizer $paymentLinkCreateRequestNormalizer
    ) {
        $this->logger = $logger;
        $this->paymentStatusValidator = $paymentStatusValidator;
        $this->paymentStatusProvider = $paymentStatusProvider;
        $this->paymentApiClient = $paymentApiClient;
        $this->paymentApiConfiguration = $paymentApiConfiguration;
        $this->paymentOrderCreateRequestNormalizer = $paymentOrderCreateRequestNormalizer;
        $this->paymentLinkCreateRequestNormalizer = $paymentLinkCreateRequestNormalizer;
    }

    public function getPaymentApiEnvironment(): PaymentApiEnvironment
    {
        return $this->paymentApiConfiguration->getPaymentApiEnvironment();
    }

    public function isPaymentStatusValid(string $paymentStatus): bool
    {
        try {
            $this->paymentStatusValidator->validate($paymentStatus);

            return true;
        } catch (ValidationException $exception) {
            $this->logger->warning(
                'Payment status is invalid',
                [
                    'exception' => $exception,
                ]
            );

            return false;
        }
    }

    public function getPaymentStatuses(): PaymentStatusCollection
    {
        return $this->paymentStatusProvider->getPaymentStatusCollection();
    }

    /**
     * Retrieves available payment methods.
     *
     * When filter is provided, returns only payment methods whose limits encompass
     * the specified amount. Otherwise, returns all available payment methods.
     *
     * @param PaymentMethodFilter|null $filter Optional filter by amount and currency
     *
     * @return PaymentMethodCollection Collection of available payment methods
     *
     * @throws IntegrationException If the API request fails
     */
    public function getPaymentMethods(?PaymentMethodFilter $filter = null): PaymentMethodCollection
    {
        try {
            $this->logger->info('Getting payment methods.', [
                'filter' => $filter !== null ? [
                    'amount' => $filter->getAmount(),
                    'currency' => $filter->getCurrency(),
                ] : null,
            ]);

            $paymentMethodCollection = $this->paymentApiClient->getPaymentCollection($filter);

            $this->logger->info('Payment methods received.');

            return $paymentMethodCollection;
        } catch (BaseException $exception) {
            $this->logger->error(
                'Could not get payment methods',
                [
                    'exception' => $exception,
                ]
            );

            throw new IntegrationException(
                'Could not get payment methods',
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @param array<string,mixed> $orderData
     *
     * @deprecated since 2.2.0; use buildPaymentOrderCreateRequestFromValues() instead — the raw-array
     *     path does not validate the source allow-list and silently forwards misspelled metadata keys.
     */
    public function buildPaymentOrderCreateRequest(array $orderData): PaymentOrderCreateRequest
    {
        return $this->paymentOrderCreateRequestNormalizer->denormalize($orderData);
    }

    /**
     * Builds a payment order create request from typed values.
     *
     * Prefer this over buildPaymentOrderCreateRequest(): the source value is validated against the
     * PaymentOrderSource allow-list and the typed Metadata prevents misspelled keys from leaking to
     * the wire as custom metadata.
     *
     * @throws ValidationException When the source value is not one of PaymentOrderSource::SOURCES.
     */
    public function buildPaymentOrderCreateRequestFromValues(
        Purchase $purchase,
        Metadata $metadata,
        ?RedirectUrls $redirectUrls = null,
        ?string $source = null
    ): PaymentOrderCreateRequest {
        $builder = (new PaymentOrderCreateRequestBuilder())
            ->setPurchase($purchase)
            ->setMetadata($metadata)
            ->setRedirectUrls($redirectUrls)
        ;

        if ($source !== null) {
            $builder->setSource($source);
        }

        return $builder->build();
    }

    /**
     * @throws IntegrationException
     */
    public function createPaymentOrder(PaymentOrderCreateRequest $request): PaymentOrderCreateResponse
    {
        try {
            $this->logger->info(
                'Starting payment order creation.',
                $request->getLoggerData()
            );

            $response = $this->paymentApiClient->createPaymentOrder($request);

            $this->logger->info(
                'Payment order created successfully.',
                $response->getLoggerData()
            );

            return $response;
        } catch (BaseException $exception) {
            $this->logger->error(
                'Payment order creation failed',
                [
                    'exception' => $exception,
                    'requestData' => $request->getLoggerData(),
                ]
            );

            throw new IntegrationException(
                $this->extractUserFacingMessage($exception, 'Payment order creation failed'),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @param array<string,mixed> $linkData
     */
    public function buildPaymentLinkCreateRequest(array $linkData): PaymentLinkCreateRequest
    {
        return $this->paymentLinkCreateRequestNormalizer->denormalize($linkData);
    }

    /**
     * @throws IntegrationException
     */
    public function createPaymentLink(PaymentLinkCreateRequest $request): PaymentLinkCreateResponse
    {
        try {
            $this->logger->info(
                'Starting payment link creation.',
                $request->getLoggerData()
            );

            $response = $this->paymentApiClient->createPaymentLink($request);

            $this->logger->info(
                'Payment link created successfully.',
                $response->getLoggerData()
            );

            return $response;
        } catch (BaseException $exception) {
            $this->logger->error(
                'Payment link creation failed',
                [
                    'exception' => $exception,
                    'requestData' => $request->getLoggerData(),
                ]
            );

            throw new IntegrationException(
                $this->extractUserFacingMessage($exception, 'Payment link creation failed'),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws IntegrationException
     */
    public function initiatePayment(
        PaymentOrderCreateRequest $orderCreateRequest,
        PaymentLinkCreateRequest $paymentLinkCreateRequest
    ): PaymentLinkCreateResponse {
        $paymentOrderCreateResponse = $this->createPaymentOrder($orderCreateRequest);

        $paymentLinkCreateRequest
            ->setOrderId($paymentOrderCreateResponse->getOrderId())
            ->getPurchase()->setAmount($paymentOrderCreateResponse->getPurchase()->getAmount())
        ;

        return $this->createPaymentLink($paymentLinkCreateRequest);
    }

    private function extractUserFacingMessage(BaseException $exception, string $fallback): string
    {
        if (!$exception instanceof ApiClientException) {
            return $fallback;
        }

        if ($exception->getCode() >= 500) {
            return $fallback;
        }

        $context = $exception->getContext();

        if ($context === '') {
            return $fallback;
        }

        $decoded = json_decode($context, true);

        if (!is_array($decoded)) {
            return $fallback;
        }

        $description = $decoded['error_description'] ?? null;

        return is_string($description) && $description !== '' ? $description : $fallback;
    }
}
