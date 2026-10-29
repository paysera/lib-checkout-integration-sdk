<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator;

use Paysera\CheckoutSdk\Entity\ProjectInfo;
use Paysera\CheckoutSdk\Entity\ProjectStatus;
use Paysera\CheckoutSdk\Exception\ValidationException;

class ProjectInfoValidator
{
    /**
     * @throws ValidationException
     */
    public function validate(ProjectInfo $projectInfo): void
    {
        $errors = [];

        if ($projectInfo->getProjectId() === '') {
            $errors['project_id'] = 'project ID is required.';
        }

        if (!in_array($projectInfo->getStatus()->getValue(), ProjectStatus::STATUSES, true)) {
            $errors['status'] = 'status has an invalid value.';
        }

        if ($errors !== []) {
            throw (new ValidationException())
                ->setContext($errors)
            ;
        }
    }
}
