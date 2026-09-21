<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Callback\Handler;

use Paysera\CheckoutSdk\Entity\CallbackEventInterface;
use Paysera\CheckoutSdk\Entity\CallbackInterface;
use Paysera\CheckoutSdk\Entity\CallbackEvent;
use Paysera\CheckoutSdk\Service\Normalizer\OrderAmountPaidCallbackNormalizer;
use Paysera\CheckoutSdk\Service\Validator\OrderAmountPaidCallbackValidator;

class OrderAmountPaidCallbackHandler implements CallbackHandlerInterface
{
    private OrderAmountPaidCallbackNormalizer $orderAmountPaidCallbackNormalizer;
    private OrderAmountPaidCallbackValidator $orderAmountPaidCallbackValidator;

    public function __construct(
        OrderAmountPaidCallbackNormalizer $orderAmountPaidCallbackNormalizer,
        OrderAmountPaidCallbackValidator $orderAmountPaidCallbackValidator
    ) {
        $this->orderAmountPaidCallbackNormalizer = $orderAmountPaidCallbackNormalizer;
        $this->orderAmountPaidCallbackValidator = $orderAmountPaidCallbackValidator;
    }

    public function supports(CallbackEventInterface $event): bool
    {
        return $event->getType() === CallbackEvent::TYPE_ORDER
            && $event->getName() === CallbackEvent::NAME_AMOUNT_PAID_UPDATED;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function handle(array $payload, CallbackEventInterface $event): CallbackInterface
    {
        $orderAmountPaidCallback = $this->orderAmountPaidCallbackNormalizer->denormalize($payload, $event);

        $this->orderAmountPaidCallbackValidator->validate($orderAmountPaidCallback);

        return $orderAmountPaidCallback;
    }
}
