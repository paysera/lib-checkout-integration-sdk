<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer;

use Paysera\CheckoutSdk\Entity\CallbackEventInterface;
use Paysera\CheckoutSdk\Entity\OrderStatusUpdatedCallback;
use Paysera\CheckoutSdk\Util\TypeConverter;

class OrderStatusUpdatedCallbackNormalizer
{
    private TypeConverter $typeConverter;

    public function __construct(TypeConverter $typeConverter)
    {
        $this->typeConverter = $typeConverter;
    }

    /**
     * @param array<string, mixed> $rawData
     */
    public function denormalize(array $rawData, CallbackEventInterface $event): OrderStatusUpdatedCallback
    {
        $orderData = $rawData['order'] ?? [];
        $reason = $this->typeConverter->convert($orderData['reason'] ?? '', TypeConverter::STRING);

        return new OrderStatusUpdatedCallback(
            $event,
            $this->typeConverter->convert($orderData['paysera_order_id'] ?? '', TypeConverter::STRING),
            $this->typeConverter->convert($orderData['merchant_order_id'] ?? '', TypeConverter::STRING),
            $this->typeConverter->convert($orderData['status'] ?? '', TypeConverter::STRING),
            $reason !== '' ? $reason : null,
            $this->typeConverter->convert($orderData['is_test'] ?? false, TypeConverter::BOOL),
            $this->denormalizeNullableInt($rawData, 'timestamp')
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function denormalizeNullableInt(array $data, string $key): ?int
    {
        return isset($data[$key]) ? $this->typeConverter->convert($data[$key], TypeConverter::INT) : null;
    }
}
