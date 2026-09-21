<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator;

use Paysera\CheckoutSdk\Entity\PaymentCurrency;
use Paysera\CheckoutSdk\Entity\PaymentOrderCreateRequest;
use Paysera\CheckoutSdk\Entity\PaymentOrder\Purchase;
use Paysera\CheckoutSdk\Entity\PaymentOrder\RedirectUrls;
use Paysera\CheckoutSdk\Entity\Metadata;
use Paysera\CheckoutSdk\Exception\ValidationException;
use Paysera\CheckoutSdk\Service\Validator\Common\ErrorBag;
use Paysera\CheckoutSdk\Service\Validator\Common\StringValidator;
use Paysera\CheckoutSdk\Service\Validator\Common\ValidatorErrorBagAppendHandler;
use Paysera\CheckoutSdk\Service\Validator\Common\ValidatorExceptionHandler;

class PaymentOrderCreateRequestValidator
{
    private const REFERENCE_MAX_LENGTH = 255;

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
     * @throws ValidationException
     */
    public function validate(PaymentOrderCreateRequest $request): void
    {
        $this->errorBag = new ErrorBag();
        $this->appendHandler->setMainErrorBag($this->errorBag);

        $this->validatePurchase($request->getPurchase());

        if ($request->getRedirectUrls() !== null) {
            $this->validateRedirectUrls($request->getRedirectUrls());
        }

        if ($request->getSource() !== null) {
            $this->stringValidator->validateMaxLength(
                $request->getSource(),
                'source',
                255,
                $this->appendHandler
            );
        }

        $this->validateMetadata($request->getMetadata());

        $this->exceptionHandler->handle($this->errorBag);
    }

    private function validatePurchase(Purchase $purchase): void
    {
        $this->validateReference($purchase->getReference());

        if ($purchase->getAmount() <= 0) {
            $this->errorBag->addError('purchase.amount', 'purchase.amount must be greater than 0');
        }

        if (!in_array($purchase->getCurrency(), PaymentCurrency::SUPPORTED_CURRENCIES, true)) {
            $this->errorBag->addError(
                'purchase.currency',
                'purchase.currency must be one of: ' . implode(', ', PaymentCurrency::SUPPORTED_CURRENCIES) . '.'
            );
        }
    }

    /**
     * The rules are applied one at a time: the error bag holds a single message per field, so
     * running them all would leave the integrator with the last one only. A blank reference also
     * fails the API's @field:NotBlank, which a bare '' comparison would let through.
     */
    private function validateReference(string $reference): void
    {
        $this->stringValidator->validateEmpty($reference, 'purchase.reference', $this->appendHandler);

        if (trim($reference) === '') {
            return;
        }

        $errorsBefore = count($this->appendHandler->getMainErrorBag()->getErrors());

        $this->stringValidator->validateMaxLength(
            $reference,
            'purchase.reference',
            self::REFERENCE_MAX_LENGTH,
            $this->appendHandler
        );

        if (count($this->appendHandler->getMainErrorBag()->getErrors()) > $errorsBefore) {
            return;
        }

        $this->stringValidator->validateReferenceCharset(
            $reference,
            'purchase.reference',
            $this->appendHandler
        );
    }

    private function validateRedirectUrls(RedirectUrls $redirectUrls): void
    {
        if ($redirectUrls->getSuccessUrl() !== null) {
            $this->stringValidator->validateMaxLength(
                $redirectUrls->getSuccessUrl(),
                'redirect_urls.success_url',
                2048,
                $this->appendHandler
            );
            $this->stringValidator->validateUrl(
                $redirectUrls->getSuccessUrl(),
                'redirect_urls.success_url',
                $this->appendHandler
            );
        }

        if ($redirectUrls->getFailureUrl() !== null) {
            $this->stringValidator->validateMaxLength(
                $redirectUrls->getFailureUrl(),
                'redirect_urls.failure_url',
                2048,
                $this->appendHandler
            );
            $this->stringValidator->validateUrl(
                $redirectUrls->getFailureUrl(),
                'redirect_urls.failure_url',
                $this->appendHandler
            );
        }

        if ($redirectUrls->getCallbackUrl() !== null) {
            $this->stringValidator->validateMaxLength(
                $redirectUrls->getCallbackUrl(),
                'redirect_urls.callback_url',
                2048,
                $this->appendHandler
            );
            $this->stringValidator->validateUrl(
                $redirectUrls->getCallbackUrl(),
                'redirect_urls.callback_url',
                $this->appendHandler
            );
        }

        if ($redirectUrls->getCancelUrl() !== null) {
            $this->stringValidator->validateMaxLength(
                $redirectUrls->getCancelUrl(),
                'redirect_urls.cancel_url',
                2048,
                $this->appendHandler
            );
            $this->stringValidator->validateUrl(
                $redirectUrls->getCancelUrl(),
                'redirect_urls.cancel_url',
                $this->appendHandler
            );
        }
    }

    private function validateMetadata(Metadata $metadata): void
    {
        if ($metadata->getPlatform() !== null) {
            $this->stringValidator->validateMaxLength(
                $metadata->getPlatform(),
                'metadata.platform',
                255,
                $this->appendHandler
            );
        }

        if ($metadata->getPlatformVersion() !== null) {
            $this->stringValidator->validateMaxLength(
                $metadata->getPlatformVersion(),
                'metadata.platform_version',
                255,
                $this->appendHandler
            );
        }

        if ($metadata->getPluginName() !== null) {
            $this->stringValidator->validateMaxLength(
                $metadata->getPluginName(),
                'metadata.plugin_name',
                255,
                $this->appendHandler
            );
        }

        if ($metadata->getPluginVersion() !== null) {
            $this->stringValidator->validateMaxLength(
                $metadata->getPluginVersion(),
                'metadata.plugin_version',
                255,
                $this->appendHandler
            );
        }

        foreach ($metadata->getCustomFields() as $key => $value) {
            if (!is_string($key) || !is_string($value)) {
                $this->errorBag->addError('metadata.custom', 'metadata custom field keys and values must be strings');
                break;
            }

            $this->stringValidator->validateMaxLength(
                $key,
                "metadata.custom.key",
                255,
                $this->appendHandler
            );
            $this->stringValidator->validateMaxLength(
                $value,
                "metadata.custom.$key",
                255,
                $this->appendHandler
            );
        }
    }
}
