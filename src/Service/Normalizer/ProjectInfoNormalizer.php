<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer;

use Paysera\CheckoutSdk\Entity\ProjectInfo;
use Paysera\CheckoutSdk\Entity\ProjectStatus;
use Paysera\CheckoutSdk\Exception\NormalizationException;

class ProjectInfoNormalizer
{
    private const PAYMENT_COLLECTION_STATUS_ENABLED = 'enabled';
    private const PAYMENT_COLLECTION_STATUS_TEST_MODE = 'test_mode';

    /**
     * @param array<string, mixed> $data
     *
     * @throws NormalizationException
     */
    public function denormalize(array $data): ProjectInfo
    {
        $paymentCollectionStatus = $data['payment_collection_status'] ?? null;

        return new ProjectInfo(
            $this->extractRequiredString($data, 'project_id'),
            new ProjectStatus($this->extractRequiredString($data, 'status')),
            $paymentCollectionStatus === self::PAYMENT_COLLECTION_STATUS_ENABLED,
            $paymentCollectionStatus === self::PAYMENT_COLLECTION_STATUS_TEST_MODE
        );
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws NormalizationException
     */
    private function extractRequiredString(array $data, string $key): string
    {
        if (!isset($data[$key]) || !is_string($data[$key]) || $data[$key] === '') {
            throw (new NormalizationException(sprintf(
                'Project info payload missing or invalid required field "%s"',
                $key
            )))->setContext($data);
        }

        return $data[$key];
    }
}
