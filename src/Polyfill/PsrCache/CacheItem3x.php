<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Polyfill\PsrCache;

use DateInterval;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Clock\ClockInterface;

/**
 * PHP 8.0+ implementation of CacheItemInterface with native type hints.
 *
 * @internal
 */
final class CacheItem3x implements CacheItemInterface
{
    private string $key;
    private ClockInterface $clock;
    private mixed $value;
    private ?DateTimeInterface $expiresAt;
    private bool $hit;

    public function __construct(string $key, ClockInterface $clock)
    {
        $this->key = $key;
        $this->clock = $clock;
        $this->expiresAt = null;
        $this->hit = false;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        if ($this->isHit()) {
            return $this->value;
        }

        return null;
    }

    public function isHit(): bool
    {
        if ($this->hit === false) {
            return false;
        }

        if ($this->expiresAt === null) {
            return true;
        }

        $currentTimestamp = $this
            ->clock
            ->now()
            ->getTimestamp()
        ;
        $expiresAtTimestamp = $this
            ->expiresAt
            ->getTimestamp()
        ;

        return $currentTimestamp < $expiresAtTimestamp;
    }

    public function set(mixed $value): static
    {
        $this->hit = true;
        $this->value = $value;

        return $this;
    }

    public function expiresAt(?DateTimeInterface $expiration): static
    {
        $this->expiresAt = $expiration;

        return $this;
    }

    public function expiresAfter(int|DateInterval|null $time): static
    {
        if ($time === null) {
            $this->expiresAt = null;
            return $this;
        }

        $interval = is_int($time) ? new DateInterval('PT' . $time . 'S') : $time;

        $this->expiresAt = $this
            ->clock
            ->now()
            ->add($interval)
        ;

        return $this;
    }
}
