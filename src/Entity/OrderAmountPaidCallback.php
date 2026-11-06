<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback\Order;

class OrderAmountPaidCallback implements CallbackInterface
{
    private CallbackEventInterface $event;
    private Order $order;
    private ?int $timestamp;

    public function __construct(
        CallbackEventInterface $event,
        Order $order,
        ?int $timestamp = null
    ) {
        $this->event = $event;
        $this->order = $order;
        $this->timestamp = $timestamp;
    }

    public function getEvent(): CallbackEventInterface
    {
        return $this->event;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }
}
