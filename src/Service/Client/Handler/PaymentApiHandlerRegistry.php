<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Client\Handler;

class PaymentApiHandlerRegistry
{
    private PaymentMethodHandler $paymentMethodHandler;
    private RefundOrderHandler $refundOrderHandler;
    private PaymentOrderHandler $paymentOrderHandler;
    private PaymentLinkHandler $paymentLinkHandler;
    private ProjectInfoHandler $projectInfoHandler;
    private ProjectWebsitesHandler $projectWebsitesHandler;

    public function __construct(
        PaymentMethodHandler $paymentMethodHandler,
        RefundOrderHandler $refundOrderHandler,
        PaymentOrderHandler $paymentOrderHandler,
        PaymentLinkHandler $paymentLinkHandler,
        ProjectInfoHandler $projectInfoHandler,
        ProjectWebsitesHandler $projectWebsitesHandler
    ) {
        $this->paymentMethodHandler = $paymentMethodHandler;
        $this->refundOrderHandler = $refundOrderHandler;
        $this->paymentOrderHandler = $paymentOrderHandler;
        $this->paymentLinkHandler = $paymentLinkHandler;
        $this->projectInfoHandler = $projectInfoHandler;
        $this->projectWebsitesHandler = $projectWebsitesHandler;
    }

    public function getPaymentMethodHandler(): PaymentMethodHandler
    {
        return $this->paymentMethodHandler;
    }

    public function getRefundOrderHandler(): RefundOrderHandler
    {
        return $this->refundOrderHandler;
    }

    public function getPaymentOrderHandler(): PaymentOrderHandler
    {
        return $this->paymentOrderHandler;
    }

    public function getPaymentLinkHandler(): PaymentLinkHandler
    {
        return $this->paymentLinkHandler;
    }

    public function getProjectInfoHandler(): ProjectInfoHandler
    {
        return $this->projectInfoHandler;
    }

    public function getProjectWebsitesHandler(): ProjectWebsitesHandler
    {
        return $this->projectWebsitesHandler;
    }
}
