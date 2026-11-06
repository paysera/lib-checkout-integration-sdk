<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\PaymentLink;

class Experience
{
    /**
     * Direct payment means that result URL will follow to direct payment method and avoid checkout page.
     */
    public const PAYMENT_FLOW_DIRECT = 'direct_payment';
    /**
     * Checkout payment means that result URL will follow to Paysera checkout page with the payment methods list.
     */
    public const PAYMENT_FLOW_CHECKOUT = 'paysera_checkout';

    private string $language;
    private ?string $paymentFlow;

    public function __construct(
        string $language,
        ?string $paymentFlow = null
    ) {
        $this->language = $language;
        $this->paymentFlow = $paymentFlow;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getPaymentFlow(): ?string
    {
        return $this->paymentFlow;
    }
}
