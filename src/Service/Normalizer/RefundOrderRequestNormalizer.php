<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer;

use Paysera\CheckoutSdk\Entity\RefundOrder\RefundUrls;
use Paysera\CheckoutSdk\Entity\RefundOrder\SelectedRefundMethod;
use Paysera\CheckoutSdk\Entity\RefundOrderRequest;
use Paysera\CheckoutSdk\Entity\RefundOrder\Refund;
use Paysera\CheckoutSdk\Entity\RefundOrder\RefundDetails;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentLink\PayerInformationNormalizer;
use Paysera\CheckoutSdk\Util\TypeConverter;

class RefundOrderRequestNormalizer
{
    private TypeConverter $typeConverter;
    private MetadataNormalizer $metadataNormalizer;
    private PayerInformationNormalizer $payerNormalizer;

    public function __construct(
        TypeConverter $typeConverter,
        MetadataNormalizer $metadataNormalizer,
        PayerInformationNormalizer $payerNormalizer
    ) {
        $this->typeConverter = $typeConverter;
        $this->metadataNormalizer = $metadataNormalizer;
        $this->payerNormalizer = $payerNormalizer;
    }

    public function normalize(RefundOrderRequest $refundOrderRequest): array
    {
        return [
            'refund' => $this->normalizeRefund($refundOrderRequest->getRefund()),
            'refund_details' => $this->normalizeRefundDetails($refundOrderRequest->getRefundDetails()),
        ];
    }

    public function denormalize(array $data): RefundOrderRequest
    {
        $refund = $this->denormalizeRefund($data['refund'] ?? []);
        $refundDetails = $this->denormalizeRefundDetails($data['refund_details'] ?? []);

        return new RefundOrderRequest(
            $refund,
            $refundDetails,
        );
    }

    private function normalizeRefund(Refund $refund): array
    {
        $refundUrls = $refund->getRefundUrls();
        $result = [
            'refund_urls' => [
                'success_url' => $refundUrls->getSuccessUrl(),
                'failure_url' => $refundUrls->getFailureUrl(),
                'callback_url' => $refundUrls->getCallbackUrl(),
            ],
        ];

        $metadata = $refund->getMetadata();
        if ($metadata === null) {
            return $result;
        }

        $normalizedMetadata = $this->metadataNormalizer->normalize($metadata);

        if ($normalizedMetadata !== []) {
            $result['metadata'] = $normalizedMetadata;
        }

        return $result;
    }

    private function normalizeRefundDetails(RefundDetails $refundDetails): array
    {
        $result = [
            'order_id' => $refundDetails->getOrderId(),
            'reference' => $refundDetails->getReference(),
            'refund_amount' => $refundDetails->getRefundAmount(),
            'currency' => $refundDetails->getCurrency(),
            'payer' => $this->payerNormalizer->normalize($refundDetails->getPayer()),
        ];

        $paymentMethod = $refundDetails->getRefundMethod();
        if (($paymentMethod !== null) && $paymentMethod->getKey() !== '') {
            $result['refund_method']['key'] = $paymentMethod->getKey();
        }

        if ($refundDetails->getReason() !== null) {
            $result['reason'] = $refundDetails->getReason();
        }

        return $result;
    }

    private function denormalizeRefund(array $refundData): Refund
    {
        $refundUrls = $refundData['refund_urls'] ?? [];

        $refundUrls = new RefundUrls(
            $this->typeConverter->convert($refundUrls['success_url'] ?? '', TypeConverter::STRING),
            $this->typeConverter->convert($refundUrls['failure_url'] ?? '', TypeConverter::STRING),
            $this->typeConverter->convert($refundUrls['callback_url'] ?? '', TypeConverter::STRING),
        );

        $metadata = $this->metadataNormalizer->denormalize($refundData['metadata'] ?? []);
        $isEmptyMetadata = $metadata->getPlatform() === null
            && $metadata->getPlatformVersion() === null
            && $metadata->getPluginName() === null
            && $metadata->getPluginVersion() === null;
        $metadata = $isEmptyMetadata ? null : $metadata;

        return new Refund($refundUrls, $metadata);
    }

    private function denormalizeRefundDetails(array $paymentDetailsData): RefundDetails
    {
        $payer = $this->payerNormalizer->denormalize($paymentDetailsData['payer'] ?? []);

        $refundMethod = null;
        if (isset($paymentDetailsData['refund_method']['key'])) {
            $refundMethod = new SelectedRefundMethod(
                $this->typeConverter->convert($paymentDetailsData['refund_method']['key'], TypeConverter::STRING)
            );
        }

        return new RefundDetails(
            $this->typeConverter->convert($paymentDetailsData['order_id'] ?? '', TypeConverter::STRING),
            $this->typeConverter->convert($paymentDetailsData['reference'] ?? '', TypeConverter::STRING),
            $this->typeConverter->convert($paymentDetailsData['refund_amount'] ?? 0, TypeConverter::INT),
            $this->typeConverter->convert($paymentDetailsData['currency'] ?? '', TypeConverter::STRING),
            $payer,
            $this->typeConverter->convert($paymentDetailsData['reason'] ?? null, TypeConverter::STRING),
            $refundMethod
        );
    }
}
