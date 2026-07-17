<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\RefundOrder;

use Paysera\CheckoutSdk\Entity\Metadata;
use Paysera\CheckoutSdk\Entity\RefundOrder\RefundUrls;

class Refund
{
    private RefundUrls $refundUrls;
    private ?Metadata $metadata;

    public function __construct(
        RefundUrls $refundUrls,
        ?Metadata $metadata = null
    ) {
        $this->refundUrls = $refundUrls;
        $this->metadata = $metadata;
    }

    public function getRefundUrls(): RefundUrls
    {
        return $this->refundUrls;
    }

    public function getMetadata(): ?Metadata
    {
        return $this->metadata;
    }
}
