<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\Collection;

use Paysera\CheckoutSdk\Entity\PaymentMethod;

/**
 * @template PaymentMethod
 * @extends Collection<PaymentMethod>
 *
 * @method PaymentMethodCollection<PaymentMethod> filter(callable $filterFunction)
 * @method void append(PaymentMethod $value)
 * @method PaymentMethod|null get(int $index = null)
 */
class PaymentMethodCollection extends Collection
{
    public function isCompatible(object $item): bool
    {
        return $item instanceof PaymentMethod;
    }

    public function current(): PaymentMethod
    {
        return parent::current();
    }

    public function getItemType(): string
    {
        return PaymentMethod::class;
    }
}
