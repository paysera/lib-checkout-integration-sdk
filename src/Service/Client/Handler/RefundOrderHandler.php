<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Client\Handler;

use Paysera\CheckoutSdk\Entity\RefundOrderRequest;
use Paysera\CheckoutSdk\Entity\RefundOrderResponse;
use Paysera\CheckoutSdk\Service\Normalizer\RefundOrderRequestNormalizer;
use Paysera\CheckoutSdk\Service\Normalizer\RefundOrderResponseNormalizer;
use Paysera\CheckoutSdk\Service\Validator\RefundOrderRequestValidator;
use Paysera\CheckoutSdk\Service\Validator\RefundOrderResponseValidator;

class RefundOrderHandler
{
    private RefundOrderRequestValidator $requestValidator;
    private RefundOrderRequestNormalizer $requestNormalizer;
    private RefundOrderResponseNormalizer $responseNormalizer;
    private RefundOrderResponseValidator $responseValidator;

    public function __construct(
        RefundOrderRequestValidator $requestValidator,
        RefundOrderRequestNormalizer $requestNormalizer,
        RefundOrderResponseNormalizer $responseNormalizer,
        RefundOrderResponseValidator $responseValidator
    ) {
        $this->requestValidator = $requestValidator;
        $this->requestNormalizer = $requestNormalizer;
        $this->responseNormalizer = $responseNormalizer;
        $this->responseValidator = $responseValidator;
    }

    public function validateRequest(RefundOrderRequest $request): void
    {
        $this->requestValidator->validate($request);
    }

    public function normalizeRequest(RefundOrderRequest $request): array
    {
        return $this->requestNormalizer->normalize($request);
    }

    public function handleResponse(array $responseData): RefundOrderResponse
    {
        $response = $this->responseNormalizer->denormalize($responseData);
        $this->responseValidator->validate($response);

        return $response;
    }
}
