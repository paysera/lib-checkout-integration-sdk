<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator;

use Paysera\CheckoutSdk\Entity\RefundOrderResponse;
use Paysera\CheckoutSdk\Entity\RefundStatus;
use Paysera\CheckoutSdk\Exception\ValidationException;

class RefundOrderResponseValidator
{
    /**
     * @throws ValidationException
     */
    public function validate(RefundOrderResponse $refundOrderResponse): void
    {
        $errors = [];
        $status = $refundOrderResponse->getStatus();

        if (!in_array($status, RefundStatus::STATUSES, true)) {
            $errors['status'] = 'status has an invalid value.';
        }

        if ($refundOrderResponse->getRefundId() === '') {
            $errors['refund_id'] = 'refund ID is required.';
        }

        if ($refundOrderResponse->getAmount() <= 0) {
            $errors['amount'] = 'amount must be greater than 0.';
        }

        if ($refundOrderResponse->getCurrency() === '') {
            $errors['currency'] = 'currency is required.';
        }

        if ($refundOrderResponse->getReference() === '') {
            $errors['reference'] = 'reference is required.';
        }

        if ($refundOrderResponse->getCreatedAt() === null) {
            $errors['created_at'] = 'createdAt is required.';
        }

        if (count($errors)) {
            throw (new ValidationException())
                ->setContext($errors)
            ;
        }
    }
}
