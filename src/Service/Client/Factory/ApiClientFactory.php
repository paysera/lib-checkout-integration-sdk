<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Client\Factory;

use Http\Client\Common\Plugin\AuthenticationPlugin;
use Http\Client\Common\PluginClient;
use Http\Message\Authentication\Bearer;
use Paysera\CheckoutSdk\Service\Client\ApiClient;
use Paysera\CheckoutSdk\Entity\PaymentApiAuthToken;
use Paysera\CheckoutSdk\Service\Validator\ResponseValidator;
use Psr\Http\Client\ClientInterface;

class ApiClientFactory
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

    public function createApiClient(?PaymentApiAuthToken $authToken = null): ApiClient
    {
        if ($authToken === null) {
            return new ApiClient($this->httpClient, $this->requestFactory, $this->responseValidator);
        }

        $pluginClient = new PluginClient(
            $this->httpClient,
            [
                new AuthenticationPlugin(new Bearer($authToken->getAccessToken())),
            ]
        );

        return new ApiClient($pluginClient, $this->requestFactory, $this->responseValidator);
    }
}
