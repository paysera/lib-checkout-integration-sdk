<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service;

use Paysera\CheckoutSdk\Entity\PaymentApiCallbackHeaders;
use Paysera\CheckoutSdk\Exception\BaseException;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentApiCallbackHeaderNormalizer;
use Paysera\CheckoutSdk\Service\Validator\PaymentApiCallbackHeadersValidator;
use Psr\Http\Message\RequestInterface;

class PaymentApiCallbackHeadersBuilder
{
    private PaymentApiCallbackHeadersValidator $paymentApiCallbackHeadersValidator;
    private PaymentApiCallbackHeaderNormalizer $paymentApiCallbackHeaderNormalizer;

    public function __construct(
        PaymentApiCallbackHeadersValidator $paymentApiCallbackHeadersValidator,
        PaymentApiCallbackHeaderNormalizer $paymentApiCallbackHeaderNormalizer
    ) {
        $this->paymentApiCallbackHeadersValidator = $paymentApiCallbackHeadersValidator;
        $this->paymentApiCallbackHeaderNormalizer = $paymentApiCallbackHeaderNormalizer;
    }

    /**
     * @throws BaseException
     */
    public function buildPaymentCallbackHeaders(RequestInterface $request): PaymentApiCallbackHeaders
    {
        $headers = [
            PaymentApiCallbackHeaders::SIGNATURE_HEADER_NAME => $request->getHeader(PaymentApiCallbackHeaders::SIGNATURE_HEADER_NAME),
            PaymentApiCallbackHeaders::SIGNATURE_ALG_HEADER_NAME => $request->getHeader(PaymentApiCallbackHeaders::SIGNATURE_ALG_HEADER_NAME),
            PaymentApiCallbackHeaders::CREATED_AT_HEADER_NAME => $request->getHeader(PaymentApiCallbackHeaders::CREATED_AT_HEADER_NAME),
            PaymentApiCallbackHeaders::REQUEST_ID_HEADER_NAME => $request->getHeader(PaymentApiCallbackHeaders::REQUEST_ID_HEADER_NAME),
            PaymentApiCallbackHeaders::CALLBACK_ID_HEADER_NAME => $request->getHeader(PaymentApiCallbackHeaders::CALLBACK_ID_HEADER_NAME),
        ];
        $unpackedHeaders = array_map(static fn (array $values) => $values[0] ?? '', $headers);

        $paymentApiCallbackHeaders = $this->paymentApiCallbackHeaderNormalizer->denormalize($unpackedHeaders);

        $this->paymentApiCallbackHeadersValidator->validate($paymentApiCallbackHeaders);

        return $paymentApiCallbackHeaders;
    }
}
