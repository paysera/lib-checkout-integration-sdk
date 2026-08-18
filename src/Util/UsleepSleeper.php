<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Util;

use Closure;

class UsleepSleeper implements SleeperInterface
{
    private const MICROSECONDS_PER_MILLISECOND = 1000;

    private Closure $sleepCallable;

    public function __construct(?callable $sleepCallable = null)
    {
        $this->sleepCallable = Closure::fromCallable($sleepCallable ?? 'usleep');
    }

    public function sleep(int $milliseconds): void
    {
        if ($milliseconds <= 0) {
            return;
        }

        ($this->sleepCallable)($milliseconds * self::MICROSECONDS_PER_MILLISECOND);
    }
}
