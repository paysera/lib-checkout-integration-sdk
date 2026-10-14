<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer;

use DateTimeImmutable;
use Paysera\CheckoutSdk\Entity\PaymentOrderCreateResponse;
use Paysera\CheckoutSdk\Exception\NormalizationException;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentOrder\PurchaseNormalizer;

class PaymentOrderCreateResponseNormalizer
{
    private MetadataNormalizer $metadataNormalizer;
    private PurchaseNormalizer $purchaseNormalizer;

    public function __construct(
        MetadataNormalizer $metadataNormalizer,
        PurchaseNormalizer $purchaseNormalizer
    ) {
        $this->metadataNormalizer = $metadataNormalizer;
        $this->purchaseNormalizer = $purchaseNormalizer;
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
            (bool) ($data['is_test'] ?? false)
        );
    }
}
