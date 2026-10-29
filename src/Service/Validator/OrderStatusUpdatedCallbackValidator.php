<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator;

use Paysera\CheckoutSdk\Entity\OrderStatusUpdatedCallback;
use Paysera\CheckoutSdk\Exception\ValidationException;

class OrderStatusUpdatedCallbackValidator
{
    /**
     * @throws ValidationException
     */
    public function validate(OrderStatusUpdatedCallback $callback): void
    {
        $errors = [];

        if ($callback->getPayseraOrderId() === '') {
            $errors['order_id'] = 'Order ID is required.';
        }
        if ($callback->getMerchantOrderId() === '') {
            $errors['merchant_order_id'] = 'Merchant order ID is required.';
        }
        // No allow-list for status or reason: a value Paysera adds later would be answered with 400 and retried for hours.
        if ($callback->getStatus() === '') {
            $errors['status'] = 'Status is required.';
        }

        if (count($errors) > 0) {
            throw (new ValidationException())
                ->setContext($errors)
            ;
        }
    }
}
