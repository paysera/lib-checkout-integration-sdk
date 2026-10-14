<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Client\Handler;

use Paysera\CheckoutSdk\Entity\PaymentOrderCreateRequest;
use Paysera\CheckoutSdk\Entity\PaymentOrderCreateResponse;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentOrderCreateRequestNormalizer;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentOrderCreateResponseNormalizer;
use Paysera\CheckoutSdk\Service\Validator\PaymentOrderCreateRequestValidator;
use Paysera\CheckoutSdk\Service\Validator\PaymentOrderCreateResponseValidator;

class PaymentOrderHandler
{
    private PaymentOrderCreateRequestValidator $requestValidator;
    private PaymentOrderCreateRequestNormalizer $requestNormalizer;
    private PaymentOrderCreateResponseNormalizer $responseNormalizer;
    private PaymentOrderCreateResponseValidator $responseValidator;

    public function __construct(
        PaymentOrderCreateRequestValidator $requestValidator,
        PaymentOrderCreateRequestNormalizer $requestNormalizer,
        PaymentOrderCreateResponseNormalizer $responseNormalizer,
        PaymentOrderCreateResponseValidator $responseValidator
    ) {
        $this->requestValidator = $requestValidator;
        $this->requestNormalizer = $requestNormalizer;
        $this->responseNormalizer = $responseNormalizer;
        $this->responseValidator = $responseValidator;
    }

    public function validateRequest(PaymentOrderCreateRequest $request): void
    {
        $this->requestValidator->validate($request);
    }

    public function normalizeRequest(PaymentOrderCreateRequest $request): array
    {
        return $this->requestNormalizer->normalize($request);
    }

    public function handleResponse(array $responseData): PaymentOrderCreateResponse
    {
        $response = $this->responseNormalizer->denormalize($responseData);
        $this->responseValidator->validate($response);

        return $response;
    }
}
