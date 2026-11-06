<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Provider;

use Paysera\CheckoutSdk\Entity\Collection\RefundStatusCollection;
use Paysera\CheckoutSdk\Entity\RefundStatus;

class RefundStatusProvider
{
    public function getRefundStatusCollection(): RefundStatusCollection
    {
        $statuses = array_map(static fn (string $status) => new RefundStatus($status), RefundStatus::STATUSES);

        return new RefundStatusCollection($statuses);
    }

    public function retrieveStatus(string $status): ?RefundStatus
    {
        $paymentStatusCollection = $this->getRefundStatusCollection();

        return $paymentStatusCollection
            ->filter(static fn (RefundStatus $refundStatus) => $refundStatus->getValue() === $status)
            ->get()
        ;
    }
}
