<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator\Common;

class ValidatorErrorBagAppendHandler implements ValidatorErrorHandlerInterface
{
    private ErrorBag $mainErrorBag;

    public function __construct()
    {
        $this->mainErrorBag = new ErrorBag();
    }

    public function setMainErrorBag(ErrorBag $mainErrorBag): void
    {
        $this->mainErrorBag = $mainErrorBag;
    }

    public function getMainErrorBag(): ErrorBag
    {
        return $this->mainErrorBag;
    }

    public function handle(ErrorBag $errorBag): void
    {
        foreach ($errorBag->getErrors() as $field => $message) {
            $this->mainErrorBag->addError($field, $message);
        }
    }
}
