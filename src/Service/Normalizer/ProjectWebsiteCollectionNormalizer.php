<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer;

use Paysera\CheckoutSdk\Entity\Collection\ProjectWebsiteCollection;
use Paysera\CheckoutSdk\Entity\ProjectWebsite;
use Paysera\CheckoutSdk\Exception\NormalizationException;

class ProjectWebsiteCollectionNormalizer
{
    private const STATUS_VERIFIED = 'verified';

    /**
     * @param array<string, mixed> $data
     *
     * @throws NormalizationException
     */
    public function denormalize(array $data): ProjectWebsiteCollection
    {
        if (!isset($data['items']) || !is_array($data['items'])) {
            throw (new NormalizationException('Project websites payload missing or non-array "items" key'))
                ->setContext($data)
            ;
        }

        $collection = new ProjectWebsiteCollection();

        foreach ($data['items'] as $item) {
            if (!is_array($item) || !isset($item['url']) || !is_string($item['url'])) {
                continue;
            }

            $collection->append(new ProjectWebsite(
                $item['url'],
                ($item['status'] ?? null) === self::STATUS_VERIFIED
            ));
        }

        return $collection;
    }
}
