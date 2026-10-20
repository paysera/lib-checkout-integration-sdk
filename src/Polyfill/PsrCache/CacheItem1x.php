<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Polyfill\PsrCache;

use DateInterval;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Clock\ClockInterface;

/**
 * PHP 7.4 compatible implementation of CacheItemInterface.
 *
 * @internal
 */
final class CacheItem1x implements CacheItemInterface
{
    private string $key;
    private ClockInterface $clock;
    /** @var mixed */
    private $value;
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

    /**
     * @return mixed
     */
    public function get()
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

    /**
     * @param mixed $value
     */
    public function set($value): self
    {
        $this->hit = true;
        $this->value = $value;

        return $this;
    }

    /**
     * @param DateTimeInterface|null $expiration
     */
    public function expiresAt($expiration): self
    {
        $this->expiresAt = $expiration;

        return $this;
    }

    /**
     * @param int|DateInterval|null $time
     */
    public function expiresAfter($time): self
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
