<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator;

use Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback;
use Paysera\CheckoutSdk\Exception\ValidationException;

class OrderAmountPaidCallbackValidator
{
    /**
     * @throws ValidationException
     */
    public function validate(OrderAmountPaidCallback $callback): void
    {
        $errors = [];

        $order = $callback->getOrder();
        $orderInfo = $order->getOrderInfo();
        $paymentLinkCollection = $order->getPaymentLinkCollection();

        if ($order->getId() === '') {
            $errors['order_id'] = 'Order ID is required.';
        }

        if ($orderInfo->getMerchantOrderId() === '') {
            $errors['merchant_order_id'] = 'Merchant order ID is required.';
        }
        if ($orderInfo->getCurrency() === '') {
            $errors['currency'] = 'Currency is required.';
        }
        if ($orderInfo->getStatus() === '') {
            $errors['status'] = 'Status is required.';
        }
        if ($orderInfo->getAmount() <= 0) {
            $errors['status'] = 'Order amount is required.';
        }
        if ($orderInfo->getAmountPaid() <= 0) {
            $errors['status'] = 'Order amount paid is required.';
        }

        if ($paymentLinkCollection->count() === 0) {
            $errors['payment_links'] = 'At least one payment link is required.';
        }

        if (count($errors) > 0) {
            throw (new ValidationException())
                ->setContext($errors)
            ;
        }
    }
}
