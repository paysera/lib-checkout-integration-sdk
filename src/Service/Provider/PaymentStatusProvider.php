<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Provider;

use Paysera\CheckoutSdk\Entity\Collection\PaymentStatusCollection;
use Paysera\CheckoutSdk\Entity\PaymentStatus;

class PaymentStatusProvider
{
    public function getPaymentStatusCollection(): PaymentStatusCollection
    {
        $statuses = array_map(static fn (string $status) => new PaymentStatus($status), PaymentStatus::STATUSES);

        return new PaymentStatusCollection($statuses);
    }

    public function retrieveStatus(string $status): ?PaymentStatus
    {
        $paymentStatusCollection = $this->getPaymentStatusCollection();

        return $paymentStatusCollection
            ->filter(static fn (PaymentStatus $paymentStatus) => $paymentStatus->getValue() === $status)
            ->get()
        ;
    }
}
