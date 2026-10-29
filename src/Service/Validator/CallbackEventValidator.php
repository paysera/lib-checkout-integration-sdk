<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator;

use Paysera\CheckoutSdk\Entity\CallbackEventInterface;
use Paysera\CheckoutSdk\Exception\ValidationException;
use Paysera\CheckoutSdk\Service\Validator\Common\ErrorBag;
use Paysera\CheckoutSdk\Service\Validator\Common\StringValidator;
use Paysera\CheckoutSdk\Service\Validator\Common\ValidatorErrorBagAppendHandler;
use Paysera\CheckoutSdk\Service\Validator\Common\ValidatorExceptionHandler;

class CallbackEventValidator
{
    private StringValidator $stringValidator;
    private ValidatorErrorBagAppendHandler $appendHandler;
    private ValidatorExceptionHandler $exceptionHandler;

    public function __construct(
        StringValidator $stringValidator,
        ValidatorErrorBagAppendHandler $appendHandler,
        ValidatorExceptionHandler $exceptionHandler
    ) {
        $this->stringValidator = $stringValidator;
        $this->appendHandler = $appendHandler;
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * @throws ValidationException
     */
    public function validate(CallbackEventInterface $event): void
    {
        $errorBag = new ErrorBag();
        $this->appendHandler->setMainErrorBag($errorBag);

        $this->stringValidator->validateEmpty($event->getType(), 'event.type', $this->appendHandler);
        $this->stringValidator->validateEmpty($event->getName(), 'event.name', $this->appendHandler);

        $this->exceptionHandler->handle($errorBag);
    }
}
