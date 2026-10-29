<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk;

use Paysera\CheckoutSdk\Service\Facade\Authorization;
use Paysera\CheckoutSdk\Service\Facade\Callbacks;
use Paysera\CheckoutSdk\Service\Facade\Infrastructure;
use Paysera\CheckoutSdk\Service\Facade\MerchantArea;
use Paysera\CheckoutSdk\Service\Facade\Payments;
use Paysera\CheckoutSdk\Service\Facade\ProjectEligibility;
use Paysera\CheckoutSdk\Service\Facade\Refunds;
use Paysera\CheckoutSdk\Service\Facade\Translations;

class SdkFacade
{
    private Infrastructure $infrastructureFacade;
    private Authorization $authorizationFacade;
    private Payments $paymentsFacade;
    private Callbacks $callbacksFacade;
    private Refunds $refundsFacade;
    private Translations $translationsFacade;
    private ProjectEligibility $projectEligibilityFacade;
    private MerchantArea $merchantAreaFacade;

    public function __construct(
        Infrastructure $infrastructureFacade,
        Authorization $authorizationFacade,
        Payments $paymentsFacade,
        Callbacks $callbacksFacade,
        Refunds $refundsFacade,
        Translations $translationsFacade,
        ProjectEligibility $projectEligibilityFacade,
        MerchantArea $merchantAreaFacade
    ) {
        $this->infrastructureFacade = $infrastructureFacade;
        $this->authorizationFacade = $authorizationFacade;
        $this->callbacksFacade = $callbacksFacade;
        $this->paymentsFacade = $paymentsFacade;
        $this->refundsFacade = $refundsFacade;
        $this->translationsFacade = $translationsFacade;
        $this->projectEligibilityFacade = $projectEligibilityFacade;
        $this->merchantAreaFacade = $merchantAreaFacade;
    }

    public function getInfrastructureFacade(): Infrastructure
    {
        return $this->infrastructureFacade;
    }

    public function getAuthorizationFacade(): Authorization
    {
        return $this->authorizationFacade;
    }

    public function getPaymentsFacade(): Payments
    {
        return $this->paymentsFacade;
    }

    public function getCallbacksFacade(): Callbacks
    {
        return $this->callbacksFacade;
    }

    public function getRefundsFacade(): Refunds
    {
        return $this->refundsFacade;
    }

    public function getTranslationsFacade(): Translations
    {
        return $this->translationsFacade;
    }

    public function getProjectEligibilityFacade(): ProjectEligibility
    {
        return $this->projectEligibilityFacade;
    }

    public function getMerchantAreaFacade(): MerchantArea
    {
        return $this->merchantAreaFacade;
    }
}
