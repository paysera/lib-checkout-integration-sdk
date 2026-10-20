<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\Collection;

use Paysera\CheckoutSdk\Entity\RefundStatus;

/**
 * @template RefundStatus
 * @extends Collection<RefundStatus>
 *
 * @method RefundStatusCollection<RefundStatus> filter(callable $filterFunction)
 * @method void append(RefundStatus $value)
 * @method RefundStatus|null get(int $index = null)
 */
class RefundStatusCollection extends Collection
{
    public function isCompatible(object $item): bool
    {
        return $item instanceof RefundStatus;
    }

    public function current(): RefundStatus
    {
        return parent::current();
    }

    public function getItemType(): string
    {
        return RefundStatus::class;
    }
}
