<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Callback\Handler;

use Paysera\CheckoutSdk\Entity\CallbackEvent;
use Paysera\CheckoutSdk\Entity\CallbackEventInterface;
use Paysera\CheckoutSdk\Entity\CallbackInterface;
use Paysera\CheckoutSdk\Service\Normalizer\OrderStatusUpdatedCallbackNormalizer;
use Paysera\CheckoutSdk\Service\Validator\OrderStatusUpdatedCallbackValidator;

class OrderStatusUpdatedCallbackHandler implements CallbackHandlerInterface
{
    private OrderStatusUpdatedCallbackNormalizer $orderStatusUpdatedCallbackNormalizer;
    private OrderStatusUpdatedCallbackValidator $orderStatusUpdatedCallbackValidator;

    public function __construct(
        OrderStatusUpdatedCallbackNormalizer $orderStatusUpdatedCallbackNormalizer,
        OrderStatusUpdatedCallbackValidator $orderStatusUpdatedCallbackValidator
    ) {
        $this->orderStatusUpdatedCallbackNormalizer = $orderStatusUpdatedCallbackNormalizer;
        $this->orderStatusUpdatedCallbackValidator = $orderStatusUpdatedCallbackValidator;
    }

    public function supports(CallbackEventInterface $event): bool
    {
        return $event->getType() === CallbackEvent::TYPE_ORDER
            && $event->getName() === CallbackEvent::NAME_STATUS_UPDATED;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function handle(array $payload, CallbackEventInterface $event): CallbackInterface
    {
        $orderStatusUpdatedCallback = $this->orderStatusUpdatedCallbackNormalizer->denormalize($payload, $event);

        $this->orderStatusUpdatedCallbackValidator->validate($orderStatusUpdatedCallback);

        return $orderStatusUpdatedCallback;
    }
}
