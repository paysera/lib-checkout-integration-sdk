<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\Collection;

use Paysera\CheckoutSdk\Entity\PaymentStatus;

/**
 * @template PaymentStatus
 * @extends Collection<PaymentStatus>
 *
 * @method PaymentStatusCollection<PaymentStatus> filter(callable $filterFunction)
 * @method void append(PaymentStatus $value)
 * @method PaymentStatus|null get(int $index = null)
 */
class PaymentStatusCollection extends Collection
{
    public function isCompatible(object $item): bool
    {
        return $item instanceof PaymentStatus;
    }

    public function current(): PaymentStatus
    {
        return parent::current();
    }

    public function getItemType(): string
    {
        return PaymentStatus::class;
    }
}
