<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Callback\Handler;

use Paysera\CheckoutSdk\Entity\CallbackEventInterface;
use Paysera\CheckoutSdk\Entity\CallbackInterface;
use Paysera\CheckoutSdk\Exception\BaseException;

interface CallbackHandlerInterface
{
    public function supports(CallbackEventInterface $event): bool;

    /**
     * @param array<string, mixed> $payload
     * @throws BaseException
     */
    public function handle(array $payload, CallbackEventInterface $event): CallbackInterface;
}
