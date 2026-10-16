<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer;

use Paysera\CheckoutSdk\Entity\PaymentApiCallbackHeaders;
use Paysera\CheckoutSdk\Util\TypeConverter;

class PaymentApiCallbackHeaderNormalizer
{
    private TypeConverter $typeConverter;

    public function __construct(TypeConverter $typeConverter)
    {
        $this->typeConverter = $typeConverter;
    }

    /**
     * @param array<string, string> $rawData
     */
    public function denormalize(array $rawData): PaymentApiCallbackHeaders
    {
        return new PaymentApiCallbackHeaders(
            $this->typeConverter->convert(
                $rawData[PaymentApiCallbackHeaders::SIGNATURE_HEADER_NAME] ?? null,
                TypeConverter::STRING
            ),
            $this->typeConverter->convert(
                $rawData[PaymentApiCallbackHeaders::SIGNATURE_ALG_HEADER_NAME] ?? null,
                TypeConverter::STRING
            ),
            $this->typeConverter->convert(
                $rawData[PaymentApiCallbackHeaders::CREATED_AT_HEADER_NAME] ?? null,
                TypeConverter::INT
            ),
            $this->typeConverter->convert(
                $rawData[PaymentApiCallbackHeaders::REQUEST_ID_HEADER_NAME] ?? null,
                TypeConverter::STRING
            ),
            $this->typeConverter->convert(
                $rawData[PaymentApiCallbackHeaders::CALLBACK_ID_HEADER_NAME] ?? null,
                TypeConverter::STRING
            )
        );
    }
}
