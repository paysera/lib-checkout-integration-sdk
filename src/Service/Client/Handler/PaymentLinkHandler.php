<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Client\Handler;

use Paysera\CheckoutSdk\Entity\PaymentLinkCreateRequest;
use Paysera\CheckoutSdk\Entity\PaymentLinkCreateResponse;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentLinkCreateRequestNormalizer;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentLinkCreateResponseNormalizer;
use Paysera\CheckoutSdk\Service\Validator\PaymentLinkCreateRequestValidator;
use Paysera\CheckoutSdk\Service\Validator\PaymentLinkCreateResponseValidator;

class PaymentLinkHandler
{
    private PaymentLinkCreateRequestValidator $requestValidator;
    private PaymentLinkCreateRequestNormalizer $requestNormalizer;
    private PaymentLinkCreateResponseNormalizer $responseNormalizer;
    private PaymentLinkCreateResponseValidator $responseValidator;

    public function __construct(
        PaymentLinkCreateRequestValidator $requestValidator,
        PaymentLinkCreateRequestNormalizer $requestNormalizer,
        PaymentLinkCreateResponseNormalizer $responseNormalizer,
        PaymentLinkCreateResponseValidator $responseValidator
    ) {
        $this->requestValidator = $requestValidator;
        $this->requestNormalizer = $requestNormalizer;
        $this->responseNormalizer = $responseNormalizer;
        $this->responseValidator = $responseValidator;
    }

    public function validateRequest(PaymentLinkCreateRequest $request): void
    {
        $this->requestValidator->validate($request);
    }

    public function normalizeRequest(PaymentLinkCreateRequest $request): array
    {
        return $this->requestNormalizer->normalize($request);
    }

    public function handleResponse(array $responseData): PaymentLinkCreateResponse
    {
        $response = $this->responseNormalizer->denormalize($responseData);
        $this->responseValidator->validate($response);

        return $response;
    }
}
