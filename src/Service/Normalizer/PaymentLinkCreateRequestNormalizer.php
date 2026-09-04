<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer;

use Paysera\CheckoutSdk\Entity\PaymentLinkCreateRequest;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentLink\ExperienceNormalizer;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentLink\PayerInformationNormalizer;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentLink\PaymentDetailsNormalizer;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentLink\PurchaseNormalizer;

class PaymentLinkCreateRequestNormalizer
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

    public function normalize(PaymentLinkCreateRequest $request): array
    {
        $result = [
            'name' => $request->getName(),
            'experience' => $this->experienceNormalizer->normalize($request->getExperience()),
            'purchase' => $this->purchaseNormalizer->normalize($request->getPurchase()),
            'metadata' => $this->metadataNormalizer->normalize($request->getMetadata()),
            'order_id' => $request->getOrderId(),
        ];

        if ($request->getLifetime() !== null) {
            $result['lifetime'] = $request->getLifetime();
        }

        if ($request->getPaymentDetails() !== null) {
            $result['payment_details'] = $this->paymentDetailsNormalizer->normalize($request->getPaymentDetails());
        }

        if ($request->getPayerInformation() !== null) {
            $result['payer_information'] = $this->payerInformationNormalizer->normalize($request->getPayerInformation());
        }

        return $result;
    }

    public function denormalize(array $data): PaymentLinkCreateRequest
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

        return new PaymentLinkCreateRequest(
            $data['name'] ?? '',
            $experience,
            $purchase,
            $metadata,
            $data['order_id'] ?? null,
            $data['lifetime'] ?? null,
            $paymentDetails,
            $payerInformation
        );
    }
}
