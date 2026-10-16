<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\Collection;

use Paysera\CheckoutSdk\Entity\PaymentCountry;

/**
 * @template PaymentCountry
 * @extends Collection<PaymentCountry>
 *
 * @method PaymentCountryCollection<PaymentCountry> filter(callable $filterFunction) Returns new collection instance with filtered items.
 * @method PaymentCountryCollection<PaymentCountry> sort(callable $compareFunction) Returns new collection instance with sorted items.
 * @method void append(PaymentCountry $value) Appends an item to the collection.
 * @method void prepend(PaymentCountry $value) Prepends an item to the collection.
 * @method PaymentCountry|null get(int $index = null) Returns item at the specified index or current position.
 * @method uniqueBy(callable $callback) Returns a new collection containing unique elements, determined by the given callback.
 */
class PaymentCountryCollection extends Collection
{
    public function isCompatible(object $item): bool
    {
        return $item instanceof PaymentCountry;
    }

    public function current(): PaymentCountry
    {
        return parent::current();
    }

    public function getItemType(): string
    {
        return PaymentCountry::class;
    }
}
