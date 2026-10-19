<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator;

use Paysera\CheckoutSdk\Entity\Metadata;
use Paysera\CheckoutSdk\Entity\RefundOrderRequest;
use Paysera\CheckoutSdk\Exception\ValidationException;
use Paysera\CheckoutSdk\Entity\RefundOrder\Refund;
use Paysera\CheckoutSdk\Entity\RefundOrder\RefundDetails;

class RefundOrderRequestValidator
{
    /**
     * @throws ValidationException
     */
    public function validate(RefundOrderRequest $request): void
    {
        $errors = [];

        $this->validateRefund($request->getRefund(), $errors);
        $this->validateRefundDetails($request->getRefundDetails(), $errors);

        if (count($errors) > 0) {
            throw (new ValidationException())
                ->setContext($errors)
            ;
        }
    }

    private function validateRefund(Refund $refund, array &$errors): void
    {
        $refundUrls = $refund->getRefundUrls();

        if (!filter_var($refundUrls->getSuccessUrl(), FILTER_VALIDATE_URL)) {
            $errors['refund.refundUrls.successUrl'] = 'refund.refundUrls.successUrl must be a valid URL.';
        }

        if (!filter_var($refundUrls->getFailureUrl(), FILTER_VALIDATE_URL)) {
            $errors['refund.refundUrls.failureUrl'] = 'refund.refundUrls.failureUrl must be a valid URL.';
        }

        if (!filter_var($refundUrls->getCallbackUrl(), FILTER_VALIDATE_URL)) {
            $errors['refund.refundUrls.callbackUrl'] = 'refund.refundUrls.callbackUrl must be a valid URL.';
        }

        if ($refund->getMetadata() !== null) {
            $this->validateRefundMetadata($refund->getMetadata(), $errors);
        }
    }

    private function validateRefundDetails(RefundDetails $refundDetails, array &$errors): void
    {
        if ($refundDetails->getOrderId() === '') {
            $errors['refundDetails.orderId'] = 'refundDetails.orderId is required.';
        }

        if ($refundDetails->getReference() === '') {
            $errors['refundDetails.reference'] = 'refundDetails.reference is required.';
        }

        if ($refundDetails->getRefundAmount() <= 0) {
            $errors['refundDetails.refundAmount'] = 'refundDetails.refundAmount must be greater than 0.';
        }

        if ($refundDetails->getCurrency() === '') {
            $errors['refundDetails.currency'] = 'refundDetails.currency is required.';
        }

        if (!filter_var($refundDetails->getPayer()->getEmail(), FILTER_VALIDATE_EMAIL)) {
            $errors['refundDetails.payer.email'] = 'refundDetails.payer.email must be a valid email address.';
        }

        if (($refundDetails->getRefundMethod() !== null) && $refundDetails->getRefundMethod()->getKey() === '') {
            $errors['refundDetails.refundMethod.key'] = 'refundDetails.refundMethod.key has an invalid value.';
        }
    }

    private function validateRefundMetadata(Metadata $metadata, array &$errors): void
    {
        if ($metadata->getPlatform() === '') {
            $errors['refund.metadata.platform'] = 'refund.metadata.platform has an invalid value.';
        }

        if ($metadata->getPlatformVersion() === '') {
            $errors['refund.metadata.platformVersion'] = 'refund.metadata.platformVersion has an invalid value.';
        }

        if ($metadata->getPluginName() === '') {
            $errors['refund.metadata.pluginName'] = 'refund.metadata.pluginName has an invalid value.';
        }

        if ($metadata->getPluginVersion() === '') {
            $errors['refund.metadata.pluginVersion'] = 'refund.metadata.pluginVersion has an invalid value.';
        }
    }
}
