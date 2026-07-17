<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator\Common;

use Paysera\CheckoutSdk\Exception\ValidationException;

class ValidatorExceptionHandler implements ValidatorErrorHandlerInterface
{
    /**
     * @throws ValidationException
     */
    public function handle(ErrorBag $errorBag): void
    {
        if ($errorBag->getErrors() !== []) {
            throw (new ValidationException())
                ->setContext($errorBag->getErrors())
            ;
        }
    }
}
