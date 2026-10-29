<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Client\Handler;

use Paysera\CheckoutSdk\Entity\ProjectInfo;
use Paysera\CheckoutSdk\Exception\NormalizationException;
use Paysera\CheckoutSdk\Exception\ValidationException;
use Paysera\CheckoutSdk\Service\Normalizer\ProjectInfoNormalizer;
use Paysera\CheckoutSdk\Service\Validator\ProjectInfoValidator;

class ProjectInfoHandler
{
    private ProjectInfoNormalizer $normalizer;
    private ProjectInfoValidator $validator;

    public function __construct(ProjectInfoNormalizer $normalizer, ProjectInfoValidator $validator)
    {
        $this->normalizer = $normalizer;
        $this->validator = $validator;
    }

    /**
     * @param array<string, mixed> $responseData
     *
     * @throws NormalizationException
     * @throws ValidationException
     */
    public function handleResponse(array $responseData): ProjectInfo
    {
        $projectInfo = $this->normalizer->denormalize($responseData);
        $this->validator->validate($projectInfo);

        return $projectInfo;
    }
}
