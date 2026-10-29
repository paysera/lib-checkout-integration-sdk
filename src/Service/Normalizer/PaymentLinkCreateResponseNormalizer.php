<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer;

use DateTimeImmutable;
use Paysera\CheckoutSdk\Entity\PaymentLinkCreateResponse;
use Paysera\CheckoutSdk\Exception\NormalizationException;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentLink\ExperienceNormalizer;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentLink\PayerInformationNormalizer;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentLink\PaymentDetailsNormalizer;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentLink\PurchaseNormalizer;

class PaymentLinkCreateResponseNormalizer
{
    private PayerInformationNormalizer $payerInformationNormalizer;
    private ExperienceNormalizer $experienceNormalizer;
    private PaymentDetailsNormalizer $paymentDetailsNormalizer;
    private PurchaseNormalizer $purchaseNormalizer;
    private MetadataNormalizer $metadataNormalizer;

    public function __construct(
        PayerInformationNormalizer $payerInformationNormalizer,
        ExperienceNormalizer $experienceNormalizer,
        PaymentDetailsNormalizer $paymentDetailsNormalizer,
        PurchaseNormalizer $purchaseNormalizer,
        MetadataNormalizer $metadataNormalizer
    ) {
        $this->payerInformationNormalizer = $payerInformationNormalizer;
        $this->experienceNormalizer = $experienceNormalizer;
        $this->paymentDetailsNormalizer = $paymentDetailsNormalizer;
        $this->purchaseNormalizer = $purchaseNormalizer;
        $this->metadataNormalizer = $metadataNormalizer;
    }

    /**
     * @throws NormalizationException
     */
    public function denormalize(array $data): PaymentLinkCreateResponse
    {
        $experience = $this->experienceNormalizer->denormalize($data['experience'] ?? []);
        $purchase = $this->purchaseNormalizer->denormalize($data['purchase'] ?? []);
        $metadata = $this->metadataNormalizer->denormalize($data['metadata'] ?? []);

        $paymentDetails = null;
        if (isset($data['payment_details'])) {
            $paymentDetails = $this->paymentDetailsNormalizer->denormalize($data['payment_details']);
        }

        $payerInformation = null;
        if (isset($data['payer_information'])) {
            $payerInformation = $this->payerInformationNormalizer->denormalize($data['payer_information']);
        }

        $createdAt = isset($data['created_at'])
            ? DateTimeImmutable::createFromFormat('U', (string) $data['created_at'])
            : new DateTimeImmutable();

        if ($createdAt === false) {
            throw (new NormalizationException())
                ->setContext($data)
            ;
        }

        $expiredAt = null;
        if (isset($data['expired_at'])) {
            $expiredAt = DateTimeImmutable::createFromFormat('U', (string) $data['expired_at']);
            if ($expiredAt === false) {
                throw (new NormalizationException())
                    ->setContext($data)
                ;
            }
        }

        return new PaymentLinkCreateResponse(
            $data['link_id'] ?? '',
            $data['order_id'] ?? '',
            $data['payment_URL'] ?? $data['payment_url'] ?? '',
            $experience,
            $purchase,
            $createdAt,
            $metadata,
            $paymentDetails,
            $payerInformation,
            $expiredAt
        );
    }
}
