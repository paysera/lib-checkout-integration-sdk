<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer\PaymentOrder;

use Paysera\CheckoutSdk\Entity\PaymentOrder\Purchase;
use Paysera\CheckoutSdk\Util\TypeConverter;

class PurchaseNormalizer
{
    private TypeConverter $typeConverter;

    public function __construct(?TypeConverter $typeConverter = null)
    {
        $this->typeConverter = $typeConverter ?? new TypeConverter();
    }

    public function normalize(Purchase $purchase): array
    {
        // Payer-set keys are written only when set, so a fixed-amount purchase serialises as it always has.
        $result = [
            'reference' => $purchase->getReference(),
        ];

        if ($purchase->getAmount() !== null) {
            $result['amount'] = $purchase->getAmount();
        }

        $result['currency'] = $purchase->getCurrency();

        if ($purchase->isPayerSetsAmount()) {
            $result['payer_sets_amount'] = true;
        }

        if ($purchase->getMinimumAmount() !== null) {
            $result['minimum_amount'] = $purchase->getMinimumAmount();
        }

        if ($purchase->getMaximumAmount() !== null) {
            $result['maximum_amount'] = $purchase->getMaximumAmount();
        }

        if ($purchase->getSuggestedAmount() !== null) {
            $result['suggested_amount'] = $purchase->getSuggestedAmount();
        }

        if ($purchase->getAmountButtons() !== []) {
            $result['amount_buttons'] = $purchase->getAmountButtons();
        }

        return $result;
    }

    public function denormalize(array $data): Purchase
    {
        return new Purchase(
            $data['reference'] ?? '',
            $this->denormalizeAmount($data, 'amount'),
            $data['currency'] ?? '',
            $this->typeConverter->convert($data['payer_sets_amount'] ?? false, TypeConverter::BOOL),
            $this->denormalizeAmount($data, 'minimum_amount'),
            $this->denormalizeAmount($data, 'maximum_amount'),
            $this->denormalizeAmount($data, 'suggested_amount'),
            $this->denormalizeAmountButtons($data)
        );
    }

    private function denormalizeAmount(array $data, string $key): ?int
    {
        return isset($data[$key]) ? (int) $data[$key] : null;
    }

    /**
     * @return list<int>
     */
    private function denormalizeAmountButtons(array $data): array
    {
        return is_array($data['amount_buttons'] ?? null) ? array_values(array_map('intval', $data['amount_buttons'])) : [];
    }
}
