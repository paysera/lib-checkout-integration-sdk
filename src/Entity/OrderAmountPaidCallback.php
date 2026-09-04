<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback\Order;

class OrderAmountPaidCallback implements CallbackInterface
{
    private CallbackEventInterface $event;
    private Order $order;

    public function __construct(
        CallbackEventInterface $event,
        Order $order
    ) {
        $this->event = $event;
        $this->order = $order;
    }

    public function getEvent(): CallbackEventInterface
    {
        return $this->event;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }
}
