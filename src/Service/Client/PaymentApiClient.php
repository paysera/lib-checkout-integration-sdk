<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Client;

use Paysera\CheckoutSdk\Entity\Collection\ProjectWebsiteCollection;
use Paysera\CheckoutSdk\Entity\ProjectInfo;
use Paysera\CheckoutSdk\Entity\RefundOrderRequest;
use Paysera\CheckoutSdk\Entity\RefundOrderResponse;
use Paysera\CheckoutSdk\Entity\PaymentMethodFilter;
use Paysera\CheckoutSdk\Entity\PaymentOrderCreateRequest;
use Paysera\CheckoutSdk\Entity\PaymentOrderCreateResponse;
use Paysera\CheckoutSdk\Entity\PaymentLinkCreateRequest;
use Paysera\CheckoutSdk\Entity\PaymentLinkCreateResponse;
use Paysera\CheckoutSdk\Exception\PaymentApiCredentialsException;
use Paysera\CheckoutSdk\Exception\RequestFactoryException;
use Paysera\CheckoutSdk\Exception\RuntimeException;
use Paysera\CheckoutSdk\Exception\ApiClientException;
use Paysera\CheckoutSdk\Exception\ValidationException;
use Paysera\CheckoutSdk\Service\Client\Factory\ApiClientFactory;
use Paysera\CheckoutSdk\Service\Client\Handler\PaymentApiHandlerRegistry;
use Paysera\CheckoutSdk\Service\Provider\PaymentApiUrlProvider;
use Paysera\CheckoutSdk\Service\Serializer\JsonSerializer;
use Paysera\CheckoutSdk\Entity\Collection\PaymentMethodCollection;
use Paysera\CheckoutSdk\Exception\PaymentApiAuthTokenException;
use Paysera\CheckoutSdk\Service\Provider\PaymentApiAuthTokenProvider;
use Paysera\CheckoutSdk\Util\MessagePayloadExtractor;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class PaymentApiClient
{
    private const HTTP_UNAUTHORIZED = 401;

    private ApiClientFactory $apiClientFactory;
    private PaymentApiUrlProvider $paymentApiUrlProvider;
    private JsonSerializer $jsonSerializer;
    private MessagePayloadExtractor $messagePayloadExtractor;
    private PaymentApiAuthTokenProvider $authTokenProvider;
    private LoggerInterface $logger;
    private PaymentApiHandlerRegistry $handlerRegistry;
    private HttpRetryExecutor $httpRetryExecutor;

    public function __construct(
        ApiClientFactory $apiClientFactory,
        PaymentApiUrlProvider $paymentApiUrlProvider,
        JsonSerializer $jsonSerializer,
        MessagePayloadExtractor $messagePayloadExtractor,
        PaymentApiAuthTokenProvider $authTokenProvider,
        LoggerInterface $logger,
        PaymentApiHandlerRegistry $handlerRegistry,
        HttpRetryExecutor $httpRetryExecutor
    ) {
        $this->apiClientFactory = $apiClientFactory;
        $this->paymentApiUrlProvider = $paymentApiUrlProvider;
        $this->jsonSerializer = $jsonSerializer;
        $this->messagePayloadExtractor = $messagePayloadExtractor;
        $this->authTokenProvider = $authTokenProvider;
        $this->logger = $logger;
        $this->handlerRegistry = $handlerRegistry;
        $this->httpRetryExecutor = $httpRetryExecutor;
    }

    /**
     * @throws RequestFactoryException
     * @throws RuntimeException
     * @throws ApiClientException
     */
    public function getPaymentCollection(?PaymentMethodFilter $filter = null): PaymentMethodCollection
    {
        $queryData = null;
        if ($filter !== null) {
            $queryData = [
                'amount' => (string) $filter->getAmount(),
                'currency' => $filter->getCurrency(),
            ];
        }

        $response = $this->executeGetRequest(
            $this->paymentApiUrlProvider->getPaymentMethodsUrl(),
            $queryData
        );

        return $this->handlerRegistry
            ->getPaymentMethodHandler()
            ->handleResponse(
                $this->deserializeResponse($response)
            )
        ;
    }

    /**
     * @throws RequestFactoryException
     * @throws RuntimeException
     * @throws ApiClientException
     */
    public function initiateRefundOrder(RefundOrderRequest $paymentRequest): RefundOrderResponse
    {
        $this->handlerRegistry
            ->getRefundOrderHandler()
            ->validateRequest($paymentRequest)
        ;

        $normalizedRequest = $this->handlerRegistry
            ->getRefundOrderHandler()
            ->normalizeRequest($paymentRequest)
        ;

        $response = $this->executeWithTokenRetry(
            function (ApiClient $apiClient) use ($normalizedRequest): ResponseInterface {
                return $apiClient->sendPostRequest(
                    $this->paymentApiUrlProvider->getInitiateRefundOrderUrl(),
                    $this->jsonSerializer->serialize($normalizedRequest)
                );
            }
        );

        return $this->handlerRegistry
            ->getRefundOrderHandler()
            ->handleResponse(
                $this->deserializeResponse($response)
            )
        ;
    }

    /**
     * @throws RequestFactoryException
     * @throws RuntimeException
     * @throws ApiClientException
     */
    public function createPaymentOrder(PaymentOrderCreateRequest $request): PaymentOrderCreateResponse
    {
        $this->handlerRegistry
            ->getPaymentOrderHandler()
            ->validateRequest($request)
        ;

        $normalizedRequest = $this->handlerRegistry
            ->getPaymentOrderHandler()
            ->normalizeRequest($request)
        ;

        $response = $this->executeWithTokenRetry(
            function (ApiClient $apiClient) use ($normalizedRequest): ResponseInterface {
                return $apiClient->sendPostRequest(
                    $this->paymentApiUrlProvider->getCreatePaymentOrderUrl(),
                    $this->jsonSerializer->serialize($normalizedRequest)
                );
            }
        );

        return $this->handlerRegistry
            ->getPaymentOrderHandler()
            ->handleResponse(
                $this->deserializeResponse($response)
            )
        ;
    }

    /**
     * @throws RequestFactoryException
     * @throws RuntimeException
     * @throws ApiClientException
     */
    public function createPaymentLink(PaymentLinkCreateRequest $request): PaymentLinkCreateResponse
    {
        $this->handlerRegistry
            ->getPaymentLinkHandler()
            ->validateRequest($request)
        ;

        $normalizedRequest = $this->handlerRegistry
            ->getPaymentLinkHandler()
            ->normalizeRequest($request)
        ;

        $response = $this->executeWithTokenRetry(
            function (ApiClient $apiClient) use ($normalizedRequest): ResponseInterface {
                return $apiClient->sendPostRequest(
                    $this->paymentApiUrlProvider->getCreatePaymentLinkUrl(),
                    $this->jsonSerializer->serialize($normalizedRequest)
                );
            }
        );

        return $this->handlerRegistry
            ->getPaymentLinkHandler()
            ->handleResponse(
                $this->deserializeResponse($response)
            )
        ;
    }

    /**
     * @throws RequestFactoryException
     * @throws RuntimeException
     * @throws ApiClientException
     */
    public function getProjectInfo(): ProjectInfo
    {
        $response = $this->executeGetRequest(
            $this->paymentApiUrlProvider->getProjectInfoUrl()
        );

        return $this->handlerRegistry
            ->getProjectInfoHandler()
            ->handleResponse(
                $this->deserializeResponse($response)
            )
        ;
    }

    /**
     * @param string $referrer Origin URL of the storefront (e.g. "https://shop.paysera.test").
     *                         Passed verbatim as the `referrer` query parameter; URL-encoding
     *                         is owned by RequestFactory downstream.
     *
     * @throws ValidationException When $referrer is blank (empty or whitespace only).
     * @throws RequestFactoryException
     * @throws RuntimeException
     * @throws ApiClientException
     */
    public function getProjectWebsites(string $referrer): ProjectWebsiteCollection
    {
        if (trim($referrer) === '') {
            throw new ValidationException('Referrer must not be blank');
        }

        $response = $this->executeGetRequest(
            $this->paymentApiUrlProvider->getProjectWebsitesUrl(),
            ['referrer' => $referrer]
        );

        return $this->handlerRegistry
            ->getProjectWebsitesHandler()
            ->handleResponse(
                $this->deserializeResponse($response)
            )
        ;
    }

    /**
     * @throws ApiClientException
     */
    public function getApiClient(): ApiClient
    {
        try {
            $authToken = $this->authTokenProvider->getAuthToken();

            return $this->apiClientFactory->createApiClient($authToken);
        } catch (PaymentApiAuthTokenException|PaymentApiCredentialsException $exception) {
            throw new ApiClientException(
                sprintf('Failed to create API client: %s', $exception->getMessage()),
                null,
                $exception
            );
        }
    }

    /**
     * @param array<string, string>|null $queryData
     *
     * @throws ApiClientException
     * @throws RequestFactoryException
     */
    private function executeGetRequest(string $url, ?array $queryData = null): ResponseInterface
    {
        return $this->executeWithTokenRetry(
            fn (ApiClient $apiClient): ResponseInterface => $this->httpRetryExecutor->execute(
                fn (): ResponseInterface => $apiClient->sendGetRequest($url, $queryData)
            )
        );
    }

    /**
     * @throws ApiClientException
     * @throws RequestFactoryException
     */
    private function executeWithTokenRetry(callable $operation): ResponseInterface
    {
        $apiClient = $this->getApiClient();

        try {
            return $operation($apiClient);
        } catch (ApiClientException $exception) {
            if ($exception->getCode() !== self::HTTP_UNAUTHORIZED) {
                throw $exception;
            }
        }

        $this->logger->warning('API returned 401, refreshing auth token and retrying request');

        try {
            $this->authTokenProvider->refreshStoredAuthToken();
        } catch (PaymentApiAuthTokenException $refreshException) {
            throw new ApiClientException(
                sprintf('Failed to refresh auth token after 401 response: %s', $refreshException->getMessage()),
                self::HTTP_UNAUTHORIZED,
                $refreshException
            );
        }

        $apiClient = $this->getApiClient();

        return $operation($apiClient);
    }

    private function deserializeResponse(ResponseInterface $response): array
    {
        return $this->jsonSerializer->deserialize(
            $this->messagePayloadExtractor->getPayload($response)
        );
    }
}
