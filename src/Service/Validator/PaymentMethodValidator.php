<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator;

use Paysera\CheckoutSdk\Entity\PaymentMethod;
use Paysera\CheckoutSdk\Exception\ValidationException;

class PaymentMethodValidator
{
    /**
     * @throws ValidationException
     */
    public function validate(PaymentMethod $paymentMethod): void
    {
        $errors = [];

        if ($paymentMethod->getKey() === '') {
            $errors['key'] = 'Payment method key is required.';
        }

        if ($paymentMethod->getTitle() === '') {
            $errors['title'] = 'Payment method title is required.';
        }

        if ($paymentMethod->getDescription() === '') {
            $errors['description'] = 'Payment method description is required.';
        }

        if ($paymentMethod->getFlow() === '') {
            $errors['flow'] = 'Payment method flow is required.';
        } elseif (
            !in_array(
                $paymentMethod->getFlow(),
                [
                    PaymentMethod::FLOW_DIRECT,
                    PaymentMethod::FLOW_REDIRECT,
                ],
                true
            )
        ) {
            $errors['flow'] = 'Payment method flow is invalid.';
        }

        if ($errors !== []) {
            throw (new ValidationException())
                ->setContext($errors)
            ;
        }
    }
}
