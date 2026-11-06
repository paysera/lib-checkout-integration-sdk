<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Util\Cache;

use Paysera\CheckoutSdk\Exception\InvalidCacheKeyException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Clock\ClockInterface;

final class InMemoryCache implements CacheItemPoolInterface
{
    private ClockInterface $clock;

    /** @var array<string, CacheItemInterface> */
    private array $items;
    /** @var array<string, CacheItemInterface> */
    private array $deferredItems;

    public function __construct(ClockInterface $clock)
    {
        $this->clock = $clock;
        $this->items = [];
        $this->deferredItems = [];
    }

    /**
     * @throws InvalidCacheKeyException
     */
    public function getItem($key): CacheItemInterface
    {
        if (!is_string($key)) {
            throw new InvalidCacheKeyException('Cache item key must be a string');
        }

        $item = $this->items[$key] ?? null;

        if ($item === null) {
            return new CacheItem($key, $this->clock);
        }

        return clone $item;
    }

    /**
     * @param string[] $keys
     * @return iterable<CacheItemInterface>
     * @throws InvalidCacheKeyException
     */
    public function getItems(array $keys = []): iterable
    {
        if ($keys === []) {
            return [];
        }

        $items = [];

        foreach ($keys as $key) {
            if (!is_string($key)) {
                throw new InvalidCacheKeyException('Cache item key must be a string');
            }
            $items[$key] = $this->getItem($key);
        }

        return $items;
    }

    /**
     * @throws InvalidCacheKeyException
     */
    public function hasItem($key): bool
    {
        return $this
            ->getItem($key)
            ->isHit()
        ;
    }

    public function clear(): bool
    {
        $this->items = [];
        $this->deferredItems = [];

        return true;
    }

    /**
     * @throws InvalidCacheKeyException
     */
    public function deleteItem($key): bool
    {
        if (!is_string($key)) {
            throw new InvalidCacheKeyException('Cache item key must be a string');
        }

        unset($this->items[$key]);

        return true;
    }

    /**
     * @param string[] $keys
     * @throws InvalidCacheKeyException
     */
    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }

        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        $this->items[$item->getKey()] = clone $item;

        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferredItems[$item->getKey()] = clone $item;

        return true;
    }

    public function commit(): bool
    {
        foreach ($this->deferredItems as $item) {
            $this->save($item);
        }

        $this->deferredItems = [];

        return true;
    }
}
