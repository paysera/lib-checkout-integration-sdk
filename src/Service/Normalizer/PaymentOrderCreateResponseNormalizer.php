<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer;

use DateTimeImmutable;
use Paysera\CheckoutSdk\Entity\PaymentOrderCreateResponse;
use Paysera\CheckoutSdk\Exception\NormalizationException;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentOrder\PurchaseNormalizer;
use Paysera\CheckoutSdk\Util\TypeConverter;

class PaymentOrderCreateResponseNormalizer
{
    private MetadataNormalizer $metadataNormalizer;
    private PurchaseNormalizer $purchaseNormalizer;
    private TypeConverter $typeConverter;

    public function __construct(
        MetadataNormalizer $metadataNormalizer,
        PurchaseNormalizer $purchaseNormalizer,
        TypeConverter $typeConverter
    ) {
        $this->metadataNormalizer = $metadataNormalizer;
        $this->purchaseNormalizer = $purchaseNormalizer;
        $this->typeConverter = $typeConverter;
    }

    /**
     * @throws NormalizationException
     */
    public function denormalize(array $data): PaymentOrderCreateResponse
    {
        $createdAt = isset($data['created_at'])
            ? DateTimeImmutable::createFromFormat('U', (string) $data['created_at'])
            : new DateTimeImmutable();

        if ($createdAt === false) {
            throw (new NormalizationException())
                ->setContext($data)
            ;
        }

        $purchase = $this->purchaseNormalizer->denormalize($data['purchase'] ?? []);
        $metadata = $this->metadataNormalizer->denormalize($data['metadata'] ?? []);

        return new PaymentOrderCreateResponse(
            $data['project_id'] ?? '',
            $data['order_id'] ?? '',
            $createdAt,
            $metadata,
            $data['source'] ?? '',
            $purchase,
            $this->typeConverter->convert($data['is_test'] ?? false, TypeConverter::BOOL),
            $this->typeConverter->convert($data['tips_enabled'] ?? false, TypeConverter::BOOL)
        );
    }
}
