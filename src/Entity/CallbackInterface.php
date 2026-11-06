<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

interface CallbackInterface
{
    public function getEvent(): CallbackEventInterface;
}
