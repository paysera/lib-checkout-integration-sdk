<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Exception;

use Psr\Container\NotFoundExceptionInterface;

class ContainerNotFoundException extends BaseException implements NotFoundExceptionInterface
{
    protected function getDefaultCode(): int
    {
        return static::E_CONTAINER;
    }
}
