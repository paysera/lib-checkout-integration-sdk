<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

interface CallbackEventInterface
{
    public function getName(): string;
    public function getType(): string;
}
