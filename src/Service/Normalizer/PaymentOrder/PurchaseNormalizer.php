<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer\PaymentOrder;

use Paysera\CheckoutSdk\Entity\PaymentOrder\Purchase;

class PurchaseNormalizer
{
    public function normalize(Purchase $purchase): array
    {
        return [
            'reference' => $purchase->getReference(),
            'amount' => $purchase->getAmount(),
            'currency' => $purchase->getCurrency(),
        ];
    }

    public function denormalize(array $data): Purchase
    {
        return new Purchase(
            $data['reference'] ?? '',
            (int) ($data['amount'] ?? 0),
            $data['currency'] ?? ''
        );
    }
}
