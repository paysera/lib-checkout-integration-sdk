<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Client\Handler;

use Paysera\CheckoutSdk\Entity\Collection\PaymentMethodCollection;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentMethodCollectionNormalizer;
use Paysera\CheckoutSdk\Service\Validator\PaymentMethodValidator;

class PaymentMethodHandler
{
    private PaymentMethodCollectionNormalizer $normalizer;
    private PaymentMethodValidator $validator;

    public function __construct(
        PaymentMethodCollectionNormalizer $normalizer,
        PaymentMethodValidator $validator
    ) {
        $this->normalizer = $normalizer;
        $this->validator = $validator;
    }

    public function handleResponse(array $responseData): PaymentMethodCollection
    {
        $collection = $this->normalizer->denormalize($responseData);

        foreach ($collection as $paymentMethod) {
            $this->validator->validate($paymentMethod);
        }

        return $collection;
    }
}
