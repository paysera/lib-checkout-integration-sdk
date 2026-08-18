<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Callback;

use Paysera\CheckoutSdk\Entity\CallbackEventInterface;
use Paysera\CheckoutSdk\Service\Callback\Handler\CallbackHandlerInterface;

class CallbackHandlerRegistry
{
    /**
     * @var CallbackHandlerInterface[]
     */
    private array $handlers;

    /**
     * @param CallbackHandlerInterface[] $handlers
     */
    public function __construct(array $handlers = [])
    {
        $this->handlers = $handlers;
    }

    public function resolve(CallbackEventInterface $event): ?CallbackHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($event)) {
                return $handler;
            }
        }

        return null;
    }
}
