<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator\Common;

interface ValidatorErrorHandlerInterface
{
    public function handle(ErrorBag $errorBag): void;
}
