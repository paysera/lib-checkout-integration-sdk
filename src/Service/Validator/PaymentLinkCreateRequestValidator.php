<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator;

use Paysera\CheckoutSdk\Entity\PaymentLinkCreateRequest;
use Paysera\CheckoutSdk\Entity\PaymentLink\Experience;
use Paysera\CheckoutSdk\Entity\PaymentLink\PaymentDetails;
use Paysera\CheckoutSdk\Entity\PaymentLink\Purchase;
use Paysera\CheckoutSdk\Entity\PaymentLink\PayerInformation;
use Paysera\CheckoutSdk\Exception\ValidationException;
use Paysera\CheckoutSdk\Service\Validator\Common\ErrorBag;
use Paysera\CheckoutSdk\Service\Validator\Common\StringValidator;
use Paysera\CheckoutSdk\Service\Validator\Common\ValidatorErrorBagAppendHandler;
use Paysera\CheckoutSdk\Service\Validator\Common\ValidatorExceptionHandler;

class PaymentLinkCreateRequestValidator
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
    public function validate(PaymentLinkCreateRequest $request): void
    {
        $this->errorBag = new ErrorBag();
        $this->appendHandler->setMainErrorBag($this->errorBag);

        $this->stringValidator->validateEmpty($request->getName(), 'name', $this->appendHandler);
        $this->stringValidator->validateMaxLength($request->getName(), 'name', 255, $this->appendHandler);
        $this->stringValidator->validateUuid($request->getOrderId() ?? '', 'order_id', $this->appendHandler);

        $this->validateLifetime($request->getLifetime());
        $this->validateExperience($request->getExperience());
        $this->validatePurchase($request->getPurchase());

        if ($request->getPaymentDetails() !== null) {
            $this->validatePaymentDetails($request->getPaymentDetails());
        }

        if ($request->getPayerInformation() !== null) {
            $this->validatePayerInformation($request->getPayerInformation());
        }

        $this->exceptionHandler->handle($this->errorBag);
    }

    private function validateLifetime(?int $lifetime): void
    {
        if ($lifetime !== null) {
            if ($lifetime < 0) {
                $this->errorBag->addError('lifetime', 'lifetime must be greater than or equal to 0');
            }
            if ($lifetime > 86313600) {
                $this->errorBag->addError('lifetime', 'lifetime cannot be greater than 86313600 seconds');
            }
        }
    }

    private function validateExperience(Experience $experience): void
    {
        if (strlen($experience->getLanguage()) !== 2) {
            $this->errorBag->addError('experience.language', 'experience.language must be a 2-character ISO 639-1 code');
        }

        if ($experience->getPaymentFlow() !== null) {
            $this->stringValidator->validateMaxLength(
                $experience->getPaymentFlow(),
                'experience.payment_flow',
                255,
                $this->appendHandler
            );
        }
    }

    private function validatePurchase(Purchase $purchase): void
    {
        if ($purchase->getAmount() <= 0) {
            $this->errorBag->addError('purchase.amount', 'purchase.amount must be greater than 0');
        }
    }

    private function validatePaymentDetails(PaymentDetails $paymentDetails): void
    {
        if ($paymentDetails->getKey() !== null) {
            $this->stringValidator->validateMaxLength(
                $paymentDetails->getKey(),
                'payment_details.key',
                255,
                $this->appendHandler
            );
        }

        if ($paymentDetails->getPurpose() !== null) {
            $this->stringValidator->validateMaxLength(
                $paymentDetails->getPurpose(),
                'payment_details.purpose',
                255,
                $this->appendHandler
            );
        }

        if ($paymentDetails->getCountryCode() !== null && strlen($paymentDetails->getCountryCode()) !== 2) {
            $this->errorBag->addError(
                'payment_details.country_code',
                'payment_details.country_code must be a 2-character ISO 3166-1 alpha-2 code.'
            );
        }
    }

    private function validatePayerInformation(PayerInformation $payerInformation): void
    {
        if ($payerInformation->getName() !== null) {
            $this->stringValidator->validateMaxLength(
                $payerInformation->getName(),
                'payer_information.name',
                150,
                $this->appendHandler
            );
        }

        if ($payerInformation->getEmail() !== null) {
            $this->stringValidator->validateEmail(
                $payerInformation->getEmail(),
                'payer_information.email',
                $this->appendHandler
            );
        }
    }
}
