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

/**
 * The websites are pre-filtered by the backend to the store URL, so an empty collection means the
 * store URL is not added to the project.
 */
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
        $storeUrlAdded = $this->isStoreUrlAdded($websites);

        if ($projectInfo->isTestMode()) {
            return $this->evaluateTestMode($projectInfo, $storeUrlAdded);
        }

        $urlVerified = $this->hasVerifiedWebsite($websites);
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
            $this->resolveReasons($projectInfo, $storeUrlAdded, $urlVerified),
            $projectInfo->getStatus()
        );
    }

    private function evaluateTestMode(ProjectInfo $projectInfo, bool $storeUrlAdded): ProjectEligibilityResponse
    {
        if ($storeUrlAdded) {
            return new ProjectEligibilityResponse(
                new ProjectEligibilityStatus(ProjectEligibilityStatus::ELIGIBLE),
                true, // paymentCollectionEnabled
                true, // urlVerified
                true, // testMode
                null, // reasons — nothing blocks a test-mode project with a matching website
                $projectInfo->getStatus()
            );
        }

        // The backend rejects every order with referrer_verification_failed while no website matches.
        // Collection reasons are not reported: test payments do not depend on them.
        $reasons = new ProjectEligibilityReasonCollection();
        $reasons->append(new ProjectEligibilityReason(ProjectEligibilityReason::STORE_URL_NOT_ADDED));

        return new ProjectEligibilityResponse(
            new ProjectEligibilityStatus(ProjectEligibilityStatus::INELIGIBLE),
            true, // paymentCollectionEnabled
            false, // urlVerified
            true, // testMode
            $reasons,
            $projectInfo->getStatus()
        );
    }

    private function resolveReasons(
        ProjectInfo $projectInfo,
        bool $storeUrlAdded,
        bool $urlVerified
    ): ProjectEligibilityReasonCollection {
        $reasons = new ProjectEligibilityReasonCollection();

        $urlReason = $this->resolveStoreUrlReason($storeUrlAdded, $urlVerified);
        if ($urlReason !== null) {
            $reasons->append($urlReason);
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

    private function resolveStoreUrlReason(bool $storeUrlAdded, bool $urlVerified): ?ProjectEligibilityReason
    {
        if (!$storeUrlAdded) {
            return new ProjectEligibilityReason(ProjectEligibilityReason::STORE_URL_NOT_ADDED);
        }

        if (!$urlVerified) {
            return new ProjectEligibilityReason(ProjectEligibilityReason::STORE_URL_NOT_CONFIRMED);
        }

        return null;
    }

    private function isStoreUrlAdded(ProjectWebsiteCollection $websites): bool
    {
        return $websites->count() > 0;
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
