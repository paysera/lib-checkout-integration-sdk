<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service;

use Paysera\CheckoutSdk\Entity\Collection\ProjectEligibilityReasonCollection;
use Paysera\CheckoutSdk\Entity\Collection\ProjectWebsiteCollection;
use Paysera\CheckoutSdk\Entity\ProjectEligibilityReason;
use Paysera\CheckoutSdk\Entity\ProjectEligibilityResponse;
use Paysera\CheckoutSdk\Entity\ProjectEligibilityStatus;
use Paysera\CheckoutSdk\Entity\ProjectInfo;
use Paysera\CheckoutSdk\Entity\ProjectStatus;
use Paysera\CheckoutSdk\Entity\ProjectWebsite;

class ProjectEligibilityEvaluator
{
    private const STATUSES_AWAITING_SUBMISSION = [
        ProjectStatus::DRAFT,
        ProjectStatus::NEEDS_CORRECTION,
    ];

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
                true,
                null,
                $projectInfo->getStatus()
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
            $urlVerified,
            false,
            $this->resolveReasons($projectInfo, $urlVerified),
            $projectInfo->getStatus()
        );
    }

    private function resolveReasons(
        ProjectInfo $projectInfo,
        bool $urlVerified
    ): ProjectEligibilityReasonCollection {
        $reasons = new ProjectEligibilityReasonCollection();

        if (!$urlVerified) {
            $reasons->append(new ProjectEligibilityReason(ProjectEligibilityReason::STORE_URL_NOT_CONFIRMED));
        }

        if ($projectInfo->isPaymentCollectionEnabled()) {
            return $reasons;
        }

        $reasons->append(new ProjectEligibilityReason(
            $this->isAwaitingSubmission($projectInfo->getStatus())
                ? ProjectEligibilityReason::PROJECT_NOT_SUBMITTED_FOR_REVIEW
                : ProjectEligibilityReason::PAYMENT_COLLECTION_DISABLED
        ));

        return $reasons;
    }

    private function isAwaitingSubmission(ProjectStatus $status): bool
    {
        return in_array($status->getValue(), self::STATUSES_AWAITING_SUBMISSION, true);
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
