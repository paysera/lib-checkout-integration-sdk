<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Exception;

use Psr\Container\ContainerExceptionInterface;

class ContainerException extends BaseException implements ContainerExceptionInterface
{
    protected function getDefaultCode(): int
    {
        return static::E_CONTAINER;
    }
}
