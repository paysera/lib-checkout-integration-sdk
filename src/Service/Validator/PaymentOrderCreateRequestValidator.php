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
use Paysera\CheckoutSdk\Service\Validator\Common\AmountButtonsTrait;
use Paysera\CheckoutSdk\Service\Validator\Common\SuggestedAmountRangeTrait;
use Paysera\CheckoutSdk\Service\Validator\Common\ValidatorErrorBagAppendHandler;
use Paysera\CheckoutSdk\Service\Validator\Common\ValidatorExceptionHandler;

class PaymentOrderCreateRequestValidator
{
    use AmountButtonsTrait;
    use SuggestedAmountRangeTrait;

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

        if ($purchase->isPayerSetsAmount()) {
            $this->validatePayerSetAmount($purchase);
        } else {
            $this->validateFixedAmount($purchase);
            $this->validateSuggestedAmountWithoutFlag($purchase);
            $this->validateAmountButtonsWithoutFlag($purchase);
        }

        if (!in_array($purchase->getCurrency(), PaymentCurrency::SUPPORTED_CURRENCIES, true)) {
            $this->errorBag->addError(
                'purchase.currency',
                'purchase.currency must be one of: ' . implode(', ', PaymentCurrency::SUPPORTED_CURRENCIES) . '.'
            );
        }
    }

    private function validateFixedAmount(Purchase $purchase): void
    {
        $amount = $purchase->getAmount();
        if ($amount === null || $amount <= 0) {
            $this->errorBag->addError('purchase.amount', 'purchase.amount must be greater than 0');
        }

        $limits = [
            'purchase.minimum_amount' => $purchase->getMinimumAmount(),
            'purchase.maximum_amount' => $purchase->getMaximumAmount(),
        ];
        foreach ($limits as $field => $limit) {
            if ($limit !== null) {
                $this->errorBag->addError(
                    $field,
                    "$field requires purchase.payer_sets_amount to be true (payer_set_amount_limits_without_flag)."
                );
            }
        }
    }

    private function validateAmountButtonsWithoutFlag(Purchase $purchase): void
    {
        if ($purchase->getAmountButtons() !== []) {
            $this->errorBag->addError(
                'purchase.amount_buttons',
                'purchase.amount_buttons requires purchase.payer_sets_amount to be true (amount_buttons_without_payer_set).'
            );
        }
    }

    private function validateSuggestedAmountWithoutFlag(Purchase $purchase): void
    {
        if ($purchase->getSuggestedAmount() !== null) {
            $this->errorBag->addError(
                'purchase.suggested_amount',
                'purchase.suggested_amount requires purchase.payer_sets_amount to be true (suggested_amount_without_payer_set).'
            );
        }
    }

    private function validatePayerSetAmount(Purchase $purchase): void
    {
        if ($purchase->getAmount() !== null) {
            $this->errorBag->addError(
                'purchase.amount',
                'purchase.amount must not be set when purchase.payer_sets_amount is true (payer_set_amount_with_amount).'
            );
        }

        // The floor and the platform maximum are API policy that may change, so they are left to the API.
        $minimum = $purchase->getMinimumAmount();
        $maximum = $purchase->getMaximumAmount();
        if ($minimum !== null && $maximum !== null && $minimum >= $maximum) {
            $this->errorBag->addError(
                'purchase.minimum_amount',
                sprintf(
                    'purchase.minimum_amount must be lower than the maximum amount %d (minimum_amount_not_below_maximum).',
                    $maximum
                )
            );

            return;
        }

        $this->validateSuggestedAmountRange($this->errorBag, $purchase->getSuggestedAmount(), $minimum, $maximum);

        if ($purchase->getAmountButtons() !== []) {
            $this->validateAmountButtons($this->errorBag, $purchase->getAmountButtons(), $minimum, $maximum);
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
