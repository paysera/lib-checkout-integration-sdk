<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use Paysera\CheckoutSdk\Entity\RefundOrder\Refund;
use Paysera\CheckoutSdk\Entity\RefundOrder\RefundDetails;

class RefundOrderRequest
{
    private Refund $refund;
    private RefundDetails $refundDetails;

    public function __construct(Refund $refund, RefundDetails $refundDetails)
    {
        $this->refund = $refund;
        $this->refundDetails = $refundDetails;
    }

    public function getRefund(): Refund
    {
        return $this->refund;
    }

    public function getRefundDetails(): RefundDetails
    {
        return $this->refundDetails;
    }

    public function getLoggerData(): array
    {
        return [
            'reference' => $this
                ->getRefundDetails()
                ->getReference(),
            'refund_amount' => $this
                ->getRefundDetails()
                ->getRefundAmount(),
            'payer_email' => $this
                ->getRefundDetails()
                ->getPayer()
                ->getEmail(),
        ];
    }
}
