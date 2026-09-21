<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer;

use DateTimeImmutable;
use DateTimeInterface;
use Paysera\CheckoutSdk\Entity\RefundOrderResponse;
use Paysera\CheckoutSdk\Util\TypeConverter;

class RefundOrderResponseNormalizer
{
    private TypeConverter $typeConverter;

    public function __construct(
        TypeConverter $typeConverter
    ) {
        $this->typeConverter = $typeConverter;
    }

    public function denormalize(array $data): RefundOrderResponse
    {
        $amount = $this->typeConverter->convert($data['amount'] ?? 0, TypeConverter::INT);
        $createdAtString = $data['created_at'] ?? '';
        $createdAt = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $createdAtString);

        return new RefundOrderResponse(
            $this->typeConverter->convert($data['status'] ?? '', TypeConverter::STRING),
            $this->typeConverter->convert($data['refund_id'] ?? '', TypeConverter::STRING),
            $amount,
            $this->typeConverter->convert($data['currency'] ?? '', TypeConverter::STRING),
            $createdAt === false ? null : $createdAt,
            $this->typeConverter->convert($data['reference'] ?? '', TypeConverter::STRING),
        );
    }
}
