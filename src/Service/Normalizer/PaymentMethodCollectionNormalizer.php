<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer;

use Paysera\CheckoutSdk\Entity\Collection\PaymentMethodCollection;
use Paysera\CheckoutSdk\Entity\PaymentMethod;
use Paysera\CheckoutSdk\Exception\BaseException;
use Paysera\CheckoutSdk\Exception\NormalizationException;
use Paysera\CheckoutSdk\Util\TypeConverter;

class PaymentMethodCollectionNormalizer
{
    private TypeConverter $typeConverter;
    private PaymentMethodNormalizer $paymentMethodNormalizer;

    public function __construct(
        TypeConverter $typeConverter,
        PaymentMethodNormalizer $paymentMethodNormalizer
    ) {
        $this->typeConverter = $typeConverter;
        $this->paymentMethodNormalizer = $paymentMethodNormalizer;
    }

    /**
     * @throws NormalizationException
     */
    public function denormalize(array $data): PaymentMethodCollection
    {
        try {
            $collection = new PaymentMethodCollection();

            foreach ($data['items'] ?? [] as $paymentMethod) {
                if (!$this->hasValidFlow($paymentMethod)) {
                    continue;
                }

                $collection->append(
                    $this->paymentMethodNormalizer->denormalize($paymentMethod)
                );
            }

            return $collection;
        } catch (BaseException $exception) {
            throw (new NormalizationException())
                ->setContext($data)
            ;
        }
    }

    private function hasValidFlow(array $paymentMethod): bool
    {
        $flow = $paymentMethod['flow'] ?? null;

        if ($flow === null) {
            return true;
        }

        $flow = $this->typeConverter->convert($flow, TypeConverter::STRING);

        return in_array($flow, [PaymentMethod::FLOW_DIRECT, PaymentMethod::FLOW_REDIRECT], true);
    }
}
