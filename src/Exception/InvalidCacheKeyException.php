<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Exception;

use Psr\Cache\InvalidArgumentException as PsrCacheInvalidArgumentException;

class InvalidCacheKeyException extends InvalidArgumentException implements PsrCacheInvalidArgumentException
{
    protected function getDefaultCode(): int
    {
        return static::E_CACHE;
    }
}
