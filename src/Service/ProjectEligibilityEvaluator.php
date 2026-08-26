<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service;

use Paysera\CheckoutSdk\Entity\Collection\ProjectWebsiteCollection;
use Paysera\CheckoutSdk\Entity\ProjectEligibilityResponse;
use Paysera\CheckoutSdk\Entity\ProjectEligibilityStatus;
use Paysera\CheckoutSdk\Entity\ProjectInfo;
use Paysera\CheckoutSdk\Entity\ProjectWebsite;

class ProjectEligibilityEvaluator
{
    public function evaluate(
        ProjectInfo $projectInfo,
        ProjectWebsiteCollection $websites
    ): ProjectEligibilityResponse {
        $urlVerified = $this->hasVerifiedWebsite($websites);

        // Test mode is eligible regardless of payment collection status or website verification.
        if ($projectInfo->isTestMode()) {
            return new ProjectEligibilityResponse(
                new ProjectEligibilityStatus(ProjectEligibilityStatus::ELIGIBLE),
                true,
                $urlVerified,
                true
            );
        }

        $paymentCollectionEnabled = $projectInfo->isPaymentCollectionEnabled();

        $status = new ProjectEligibilityStatus(
            $paymentCollectionEnabled && $urlVerified
                ? ProjectEligibilityStatus::ELIGIBLE
                : ProjectEligibilityStatus::INELIGIBLE
        );

        return new ProjectEligibilityResponse(
            $status,
            $paymentCollectionEnabled,
            $urlVerified
        );
    }

    private function hasVerifiedWebsite(ProjectWebsiteCollection $websites): bool
    {
        /** @var ProjectWebsite $website */
        foreach ($websites as $website) {
            if ($website->isVerified()) {
                return true;
            }
        }

        return false;
    }
}
