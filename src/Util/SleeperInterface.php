<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Util;

interface SleeperInterface
{
    public function sleep(int $milliseconds): void;
}
