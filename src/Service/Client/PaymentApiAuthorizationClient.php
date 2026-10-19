<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Client;

use Paysera\CheckoutSdk\Exception\ApiClientException;
use Paysera\CheckoutSdk\Exception\JwtValidationException;
use Paysera\CheckoutSdk\Exception\RequestFactoryException;
use Paysera\CheckoutSdk\Exception\RuntimeException;
use Paysera\CheckoutSdk\Exception\SerializerException;
use Paysera\CheckoutSdk\Exception\ValidationException;
use Paysera\CheckoutSdk\Entity\PaymentApiAuthToken;
use Paysera\CheckoutSdk\Entity\PaymentApiCredentials;
use Paysera\CheckoutSdk\Service\Client\Factory\ApiClientFactory;
use Paysera\CheckoutSdk\Service\Factory\PaymentApiAuthTokenFactory;
use Paysera\CheckoutSdk\Service\JWT\PaymentApiJwtValidatorInterface;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentApiCredentialsNormalizer;
use Paysera\CheckoutSdk\Service\Provider\PaymentApiUrlProvider;
use Paysera\CheckoutSdk\Service\Serializer\JsonSerializer;
use Paysera\CheckoutSdk\Service\Validator\PaymentApiCredentialsValidator;
use Paysera\CheckoutSdk\Util\MessagePayloadExtractor;

class PaymentApiAuthorizationClient
{
    private ApiClientFactory $apiClientFactory;
    private PaymentApiUrlProvider $paymentApiUrlProvider;
    private PaymentApiCredentialsNormalizer $paymentApiCredentialsNormalizer;
    private PaymentApiCredentialsValidator $paymentApiCredentialsValidator;
    private JsonSerializer $jsonSerializer;
    private MessagePayloadExtractor $messagePayloadExtractor;
    private PaymentApiJwtValidatorInterface $jwtValidator;
    private PaymentApiAuthTokenFactory $paymentApiAuthTokenFactory;

    public function __construct(
        ApiClientFactory $apiClientFactory,
        PaymentApiUrlProvider $paymentApiUrlProvider,
        PaymentApiCredentialsValidator $paymentApiCredentialsValidator,
        PaymentApiCredentialsNormalizer $paymentApiCredentialsNormalizer,
        JsonSerializer $jsonSerializer,
        MessagePayloadExtractor $messagePayloadExtractor,
        PaymentApiJwtValidatorInterface $jwtValidator,
        PaymentApiAuthTokenFactory $paymentApiAuthTokenFactory
    ) {
        $this->apiClientFactory = $apiClientFactory;
        $this->paymentApiUrlProvider = $paymentApiUrlProvider;
        $this->paymentApiCredentialsValidator = $paymentApiCredentialsValidator;
        $this->paymentApiCredentialsNormalizer = $paymentApiCredentialsNormalizer;
        $this->jsonSerializer = $jsonSerializer;
        $this->messagePayloadExtractor = $messagePayloadExtractor;
        $this->jwtValidator = $jwtValidator;
        $this->paymentApiAuthTokenFactory = $paymentApiAuthTokenFactory;
    }

    /**
     * @throws ApiClientException
     * @throws RequestFactoryException
     * @throws RuntimeException
     * @throws SerializerException
     * @throws ValidationException
     * @throws JwtValidationException
     */
    public function getAuthToken(PaymentApiCredentials $apiCredentials): PaymentApiAuthToken
    {
        $this->paymentApiCredentialsValidator->validate($apiCredentials);

        $apiClient = $this->apiClientFactory->createApiClient();

        $normalizedCredentials = $this->paymentApiCredentialsNormalizer->normalize($apiCredentials);
        $formData = array_merge($normalizedCredentials, ['grant_type' => 'client_credentials']);

        $response = $apiClient->sendPostRequest(
            $this->paymentApiUrlProvider->getAuthUrl(),
            http_build_query($formData),
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ]
        );

        $authTokenResponse = $this->jsonSerializer->deserialize(
            $this->messagePayloadExtractor->getPayload($response)
        );

        $accessToken = $authTokenResponse['access_token'] ?? '';
        $this->jwtValidator->validate($accessToken);

        return $this->paymentApiAuthTokenFactory
            ->create(
                $authTokenResponse['access_token'] ?? '',
            )
        ;
    }
}
