<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer\PaymentLink;

use Paysera\CheckoutSdk\Entity\PaymentLink\Purchase;

class PurchaseNormalizer
{
    public function normalize(Purchase $purchase): array
    {
        return [
            'amount' => $purchase->getAmount(),
        ];
    }

    public function denormalize(array $data): Purchase
    {
        return new Purchase(
            (int) ($data['amount'] ?? 0)
        );
    }
}
