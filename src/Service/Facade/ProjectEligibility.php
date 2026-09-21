<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Facade;

use Paysera\CheckoutSdk\Entity\ProjectEligibilityCheckRequest;
use Paysera\CheckoutSdk\Entity\ProjectEligibilityResponse;
use Paysera\CheckoutSdk\Entity\ProjectEligibilityStatus;
use Paysera\CheckoutSdk\Exception\BaseException;
use Paysera\CheckoutSdk\Service\Client\PaymentApiClient;
use Paysera\CheckoutSdk\Service\ProjectEligibilityEvaluator;
use Psr\Log\LoggerInterface;

/**
 * Unlike the other SDK facades, this one does not rethrow API/normalization/validation
 * failures as IntegrationException. Such failures are surfaced as a ProjectEligibilityResponse
 * with status FAILED_TO_CHECK and null flags, so plugins can render a deterministic Retry UX
 * without wrapping the call in try/catch. See Confluence "Implement handling logic in SDK" (AC6, AC7).
 */
class ProjectEligibility
{
    private LoggerInterface $logger;
    private PaymentApiClient $paymentApiClient;
    private ProjectEligibilityEvaluator $evaluator;

    public function __construct(
        LoggerInterface $logger,
        PaymentApiClient $paymentApiClient,
        ProjectEligibilityEvaluator $evaluator
    ) {
        $this->logger = $logger;
        $this->paymentApiClient = $paymentApiClient;
        $this->evaluator = $evaluator;
    }

    public function checkProjectEligibility(ProjectEligibilityCheckRequest $request): ProjectEligibilityResponse
    {
        $this->logger->info('Checking project eligibility.', $request->getLoggerData());

        try {
            $projectInfo = $this->paymentApiClient->getProjectInfo();
            $websites = $this->paymentApiClient->getProjectWebsites($request->getStoreUrl());

            $response = $this->evaluator->evaluate($projectInfo, $websites);
        } catch (BaseException $exception) {
            $this->logger->error(
                'Project eligibility check failed',
                [
                    'exception' => $exception,
                    'request' => $request->getLoggerData(),
                ]
            );

            return new ProjectEligibilityResponse(
                new ProjectEligibilityStatus(ProjectEligibilityStatus::FAILED_TO_CHECK),
                null,
                null,
                null
            );
        }

        $this->logger->info('Project eligibility check completed.', $response->getLoggerData());

        return $response;
    }
}
