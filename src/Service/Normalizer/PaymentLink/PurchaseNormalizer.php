<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer\PaymentLink;

use Paysera\CheckoutSdk\Entity\PaymentLink\Purchase;
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
        $result = [];

        if ($purchase->getAmount() !== null) {
            $result['amount'] = $purchase->getAmount();
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
        return (new Purchase(
            $this->denormalizeAmount($data, 'amount'),
            $this->denormalizeAmount($data, 'suggested_amount'),
            $this->denormalizeAmountButtons($data)
        ))
            ->setPayerSetAmount(
                $this->typeConverter->convert($data['payer_sets_amount'] ?? false, TypeConverter::BOOL),
                $this->denormalizeAmount($data, 'minimum_amount'),
                $this->denormalizeAmount($data, 'maximum_amount')
            )
        ;
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
