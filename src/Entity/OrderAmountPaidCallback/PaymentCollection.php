<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback;

use Paysera\CheckoutSdk\Entity\Collection\Collection;

/**
 * @template Payment
 * @extends Collection<Payment>
 *
 * @method PaymentCollection<Payment> filter(callable $filterFunction)
 * @method void append(Payment $value)
 * @method Payment|null get(int $index = null)
 */
class PaymentCollection extends Collection
{
    public function isCompatible(object $item): bool
    {
        return $item instanceof Payment;
    }

    public function current(): Payment
    {
        return parent::current();
    }

    public function getItemType(): string
    {
        return Payment::class;
    }
}
