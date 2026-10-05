<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Client;

use Fig\Http\Message\RequestMethodInterface;
use Paysera\CheckoutSdk\Exception\ApiClientException;
use Paysera\CheckoutSdk\Service\Client\Factory\RequestFactory;
use Paysera\CheckoutSdk\Exception\RequestFactoryException;
use Paysera\CheckoutSdk\Service\Validator\ResponseValidator;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ApiClient
{
    private ClientInterface $httpClient;
    private RequestFactory $requestFactory;
    private ResponseValidator $responseValidator;

    public function __construct(
        ClientInterface $httpClient,
        RequestFactory $requestFactory,
        ResponseValidator $responseValidator
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->responseValidator = $responseValidator;
    }

    /**
     * @throws RequestFactoryException
     * @throws ApiClientException
     */
    public function sendGetRequest(string $uri, ?array $queryData = null): ResponseInterface
    {
        $request = $this->requestFactory->createRequest(
            RequestMethodInterface::METHOD_GET,
            $uri,
            $queryData
        );
        $request = $request->withHeader('Accept', 'application/json');

        return $this->sendRequest($request);
    }

    /**
     * @throws RequestFactoryException
     * @throws ApiClientException
     */
    public function sendPostRequest(string $uri, ?string $body = null, ?array $headers = null): ResponseInterface
    {
        $request = $this->requestFactory->createRequest(
            RequestMethodInterface::METHOD_POST,
            $uri,
            null,
            $body,
            $headers
        );

        if (!$request->hasHeader('Content-Type')) {
            $request = $request->withHeader('Content-Type', 'application/json');
        }
        if (!$request->hasHeader('Accept')) {
            $request = $request->withHeader('Accept', 'application/json');
        }

        return $this->sendRequest($request);
    }

    /**
     * @throws ApiClientException
     */
    protected function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (Throwable $exception) {
            throw new ApiClientException('Failed to send request', null, $exception);
        }

        $this->responseValidator->validate($response);

        return $response;
    }
}
