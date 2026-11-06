<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator;

use Paysera\CheckoutSdk\Exception\ValidationException;
use Paysera\CheckoutSdk\Service\Provider\RefundStatusProvider;

class RefundStatusValidator
{
    private RefundStatusProvider $refundStatusProvider;

    public function __construct(RefundStatusProvider $refundStatusProvider)
    {
        $this->refundStatusProvider = $refundStatusProvider;
    }

    /**
     * @throws ValidationException
     */
    public function validate(string $status): void
    {
        $status = trim($status);

        if ($this->refundStatusProvider->retrieveStatus($status) === null) {
            throw (new ValidationException())
                ->setContext(['status' => sprintf('Refund status "%s" does not exist.', $status)])
            ;
        }
    }
}
