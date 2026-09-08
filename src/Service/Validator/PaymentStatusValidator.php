<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator;

use Paysera\CheckoutSdk\Exception\ValidationException;
use Paysera\CheckoutSdk\Service\Provider\PaymentStatusProvider;

class PaymentStatusValidator
{
    private PaymentStatusProvider $paymentStatusProvider;

    public function __construct(PaymentStatusProvider $paymentStatusProvider)
    {
        $this->paymentStatusProvider = $paymentStatusProvider;
    }

    /**
     * @throws ValidationException
     */
    public function validate(string $status): void
    {
        $status = trim($status);

        if ($this->paymentStatusProvider->retrieveStatus($status) === null) {
            throw (new ValidationException())
                ->setContext(['status' => sprintf('Payment status "%s" does not exist.', $status)])
            ;
        }
    }
}
