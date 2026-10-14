<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Client\Handler;

use Paysera\CheckoutSdk\Entity\Collection\ProjectWebsiteCollection;
use Paysera\CheckoutSdk\Exception\NormalizationException;
use Paysera\CheckoutSdk\Exception\ValidationException;
use Paysera\CheckoutSdk\Service\Normalizer\ProjectWebsiteCollectionNormalizer;
use Paysera\CheckoutSdk\Service\Validator\ProjectWebsiteCollectionValidator;

class ProjectWebsitesHandler
{
    private ProjectWebsiteCollectionNormalizer $normalizer;
    private ProjectWebsiteCollectionValidator $validator;

    public function __construct(
        ProjectWebsiteCollectionNormalizer $normalizer,
        ProjectWebsiteCollectionValidator $validator
    ) {
        $this->normalizer = $normalizer;
        $this->validator = $validator;
    }

    /**
     * @param array<string, mixed> $responseData
     *
     * @throws NormalizationException
     * @throws ValidationException
     */
    public function handleResponse(array $responseData): ProjectWebsiteCollection
    {
        $collection = $this->normalizer->denormalize($responseData);
        $this->validator->validate($collection);

        return $collection;
    }
}
