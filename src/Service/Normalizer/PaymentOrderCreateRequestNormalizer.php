<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer;

use Paysera\CheckoutSdk\Entity\PaymentOrderCreateRequest;
use Paysera\CheckoutSdk\Entity\PaymentOrder\RedirectUrls;
use Paysera\CheckoutSdk\Service\Normalizer\PaymentOrder\PurchaseNormalizer;
use Paysera\CheckoutSdk\Util\TypeConverter;

class PaymentOrderCreateRequestNormalizer
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

    public function normalize(PaymentOrderCreateRequest $request): array
    {
        $result = [
            'purchase' => $this->purchaseNormalizer->normalize($request->getPurchase()),
            'metadata' => $this->metadataNormalizer->normalize($request->getMetadata()),
        ];

        if ($request->getRedirectUrls() !== null) {
            $result['redirect_urls'] = $this->normalizeRedirectUrls($request->getRedirectUrls());
        }

        if ($request->getSource() !== null) {
            $result['source'] = $request->getSource();
        }

        // Written only when set, so a request without tips serialises as it always has.
        if ($request->isTipsEnabled()) {
            $result['tips_enabled'] = true;
        }

        return $result;
    }

    public function denormalize(array $data): PaymentOrderCreateRequest
    {
        $purchase = $this->purchaseNormalizer->denormalize($data['purchase'] ?? []);
        $metadata = $this->metadataNormalizer->denormalize($data['metadata'] ?? []);

        $redirectUrls = null;
        if (isset($data['redirect_urls'])) {
            $redirectUrls = $this->denormalizeRedirectUrls($data['redirect_urls']);
        }

        return new PaymentOrderCreateRequest(
            $purchase,
            $metadata,
            $redirectUrls,
            $data['source'] ?? null,
            $this->typeConverter->convert($data['tips_enabled'] ?? false, TypeConverter::BOOL)
        );
    }

    private function normalizeRedirectUrls(RedirectUrls $redirectUrls): array
    {
        $result = [];

        if ($redirectUrls->getSuccessUrl() !== null) {
            $result['success_url'] = $redirectUrls->getSuccessUrl();
        }

        if ($redirectUrls->getFailureUrl() !== null) {
            $result['failure_url'] = $redirectUrls->getFailureUrl();
        }

        if ($redirectUrls->getCallbackUrl() !== null) {
            $result['callback_url'] = $redirectUrls->getCallbackUrl();
        }

        if ($redirectUrls->getCancelUrl() !== null) {
            $result['cancel_url'] = $redirectUrls->getCancelUrl();
        }

        return $result;
    }

    private function denormalizeRedirectUrls(array $data): RedirectUrls
    {
        return new RedirectUrls(
            $data['success_url'] ?? null,
            $data['failure_url'] ?? null,
            $data['callback_url'] ?? null,
            $data['cancel_url'] ?? null
        );
    }
}
