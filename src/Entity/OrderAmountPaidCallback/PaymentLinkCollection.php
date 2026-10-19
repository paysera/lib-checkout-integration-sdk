<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback;

use Paysera\CheckoutSdk\Entity\Collection\Collection;

/**
 * @template PaymentLink
 * @extends Collection<PaymentLink>
 *
 * @method PaymentLinkCollection<PaymentLink> filter(callable $filterFunction)
 * @method void append(PaymentLink $value)
 * @method PaymentLink|null get(int $index = null)
 */
class PaymentLinkCollection extends Collection
{
    public function isCompatible(object $item): bool
    {
        return $item instanceof PaymentLink;
    }

    public function current(): PaymentLink
    {
        return parent::current();
    }

    public function getItemType(): string
    {
        return PaymentLink::class;
    }
}
