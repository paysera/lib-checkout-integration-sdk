<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator;

use Paysera\CheckoutSdk\Entity\PaymentOrderCreateRequest;
use Paysera\CheckoutSdk\Entity\PaymentOrderCreateResponse;
use Paysera\CheckoutSdk\Exception\ValidationException;
use Paysera\CheckoutSdk\Service\Validator\Common\ErrorBag;
use Paysera\CheckoutSdk\Service\Validator\Common\StringValidator;
use Paysera\CheckoutSdk\Service\Validator\Common\ValidatorErrorBagAppendHandler;
use Paysera\CheckoutSdk\Service\Validator\Common\ValidatorExceptionHandler;

class PaymentOrderCreateResponseValidator
{
    private StringValidator $stringValidator;
    private ValidatorErrorBagAppendHandler $appendHandler;
    private ValidatorExceptionHandler $exceptionHandler;
    private ErrorBag $errorBag;

    public function __construct(
        StringValidator $stringValidator,
        ValidatorErrorBagAppendHandler $appendHandler,
        ValidatorExceptionHandler $exceptionHandler
    ) {
        $this->stringValidator = $stringValidator;
        $this->appendHandler = $appendHandler;
        $this->exceptionHandler = $exceptionHandler;

        $this->errorBag = new ErrorBag();
    }

    /**
     * @param PaymentOrderCreateRequest|null $request the request the response answers; checked for dropped tips
     *
     * @throws ValidationException
     */
    public function validate(PaymentOrderCreateResponse $response, ?PaymentOrderCreateRequest $request = null): void
    {
        $this->errorBag = new ErrorBag();
        $this->appendHandler->setMainErrorBag($this->errorBag);

        $this->stringValidator->validateUuid($response->getProjectId(), 'project_id', $this->appendHandler);
        $this->stringValidator->validateUuid($response->getOrderId(), 'order_id', $this->appendHandler);
        $this->stringValidator->validateEmpty($response->getSource(), 'source', $this->appendHandler);

        $purchase = $response->getPurchase();
        $this->stringValidator->validateEmpty($purchase->getReference(), 'purchase.reference', $this->appendHandler);
        $this->stringValidator->validateEmpty($purchase->getCurrency(), 'purchase.currency', $this->appendHandler);

        $amount = $purchase->getAmount();
        if (!$purchase->isPayerSetsAmount() && ($amount === null || $amount <= 0)) {
            $this->errorBag->addError('purchase.amount', 'purchase.amount must be greater than 0');
        }

        // An API without tips support ignores the unknown field and creates the order without a tip.
        if ($request !== null && $request->isTipsEnabled() && !$response->isTipsEnabled()) {
            $this->errorBag->addError('tips_enabled', 'tips_enabled was requested but the order was created without tips');
        }

        $this->exceptionHandler->handle($this->errorBag);
    }
}
