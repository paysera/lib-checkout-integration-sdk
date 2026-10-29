<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator;

use Paysera\CheckoutSdk\Entity\PaymentLinkCreateResponse;
use Paysera\CheckoutSdk\Exception\ValidationException;
use Paysera\CheckoutSdk\Service\Validator\Common\ErrorBag;
use Paysera\CheckoutSdk\Service\Validator\Common\StringValidator;
use Paysera\CheckoutSdk\Service\Validator\Common\ValidatorErrorBagAppendHandler;
use Paysera\CheckoutSdk\Service\Validator\Common\ValidatorExceptionHandler;

class PaymentLinkCreateResponseValidator
{
    private StringValidator $stringValidator;
    private ValidatorErrorBagAppendHandler $appendHandler;
    private ValidatorExceptionHandler $exceptionHandler;
    private ErrorBag  $errorBag;

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
     * @throws ValidationException
     */
    public function validate(PaymentLinkCreateResponse $response): void
    {
        $this->errorBag = new ErrorBag();
        $this->appendHandler->setMainErrorBag($this->errorBag);

        $this->stringValidator->validateUuid($response->getLinkId(), 'link_id', $this->appendHandler);
        $this->stringValidator->validateUuid($response->getOrderId(), 'order_id', $this->appendHandler);
        $this->stringValidator->validateUrl($response->getPaymentUrl(), 'payment_url', $this->appendHandler);
        $this->validateTimestamps($response);

        $this->exceptionHandler->handle($this->errorBag);
    }

    private function validateTimestamps(PaymentLinkCreateResponse $response): void
    {
        $createdAt = $response->getCreatedAt();
        $expiredAt = $response->getExpiredAt();

        if ($createdAt->getTimestamp() <= 0) {
            $this->errorBag->addError('created_at', 'created_at must be a positive timestamp');
        }

        if ($expiredAt === null) {
            return;
        }

        if ($expiredAt->getTimestamp() <= 0) {
            $this->errorBag->addError('expired_at', 'expired_at must be a positive timestamp');
            return;
        }

        if ($expiredAt->getTimestamp() < $createdAt->getTimestamp()) {
            $this->errorBag->addError('expired_at', 'expired_at must be greater than created_at');
        }
    }
}
