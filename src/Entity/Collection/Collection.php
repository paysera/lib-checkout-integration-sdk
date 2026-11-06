<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\Collection;

use Countable;
use Iterator;
use Paysera\CheckoutSdk\Exception\InvalidTypeException;

/**
 * @phpstan-consistent-constructor
 * @template ItemInterface
 * @implements Iterator<ItemInterface>
 */
abstract class Collection implements Iterator, Countable
{
    private int $position;
    private array $array;

    public function __construct(array $array = [])
    {
        $this->position = 0;
        $this->exchangeArray($array);
    }

    abstract public function getItemType(): string;

    abstract public function isCompatible(object $item): bool;

    public function count(): int
    {
        return count($this->array);
    }

    public function current(): ItemInterface
    {
        return $this->array[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->array[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Returns new collection instance with filtered items.
     * @param callable $filterFunction
     * @return Collection<ItemInterface>
     */
    public function filter(callable $filterFunction): Collection
    {
        $filteredArray = array_filter($this->array, $filterFunction);

        return new static(array_values($filteredArray));
    }

    /**
     * Returns new collection instance with sorted items.
     *
     * @param callable(ItemInterface, ItemInterface): int $compareFunction Comparison function that returns
     *                                                                      < 0 if first item is less than second,
     *                                                                      0 if equal, > 0 if greater
     * @return Collection<ItemInterface>
     */
    public function sort(callable $compareFunction): Collection
    {
        $sortedArray = $this->array;
        usort($sortedArray, $compareFunction);

        return new static($sortedArray);
    }

    /**
     * @throws InvalidTypeException
     */
    public function exchangeArray(array $array): void
    {
        $isCompatible = array_reduce($array, fn ($carry, $item) => $carry && $this->isCompatible($item), true);

        if (!$isCompatible) {
            throw new InvalidTypeException('Invalid item type provided. Expected: ' . $this->getItemType());
        }

        $this->rewind();
        $this->array = $array;
    }

    /**
     * @param ItemInterface $value
     * @throws InvalidTypeException
     */
    public function append($value): void
    {
        if (!$this->isCompatible($value)) {
            throw new InvalidTypeException('Invalid item type provided. Expected: ' . $this->getItemType());
        }
        $this->array[] = $value;
    }

    /**
     * @param ItemInterface $value
     * @throws InvalidTypeException
     */
    public function prepend($value): void
    {
        if ($this->isCompatible($value) === false) {
            throw new InvalidTypeException($this->getItemType());
        }
        array_unshift($this->array, $value);
    }

    /**
     * @return ItemInterface|null
     * @param null|int $index
     */
    public function get(?int $index = null)
    {
        return $this->array[$index ?? $this->position] ?? null;
    }

    /**
     * Returns a new collection containing unique elements, determined by the given callback.
     *
     * The callback must return a scalar or string value used as a unique key.
     * The first occurrence of each unique key is preserved (stable order).
     *
     * Example:
     * ```php
     * $unique = $collection->uniqueBy(
     *     static fn(ItemInterface $item) => $item->getSomeKey()
     * );
     * ```
     *
     * @param callable $callback Function that receives an item and returns a unique key.
     * @return static New collection containing only unique items.
     */
    public function uniqueBy(callable $callback): self
    {
        $uniqueItems = [];
        $seenKeys = [];

        foreach ($this->array as $item) {
            $key = $callback($item);

            // normalize to string for array key
            $keyString = is_scalar($key) ? (string) $key : md5(serialize($key));

            if (array_key_exists($keyString, $seenKeys) === false) {
                $seenKeys[$keyString] = true;
                $uniqueItems[] = $item;
            }
        }

        return new static($uniqueItems);
    }
}
