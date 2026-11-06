<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator;

use Paysera\CheckoutSdk\Entity\ProjectInfo;
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

        if ($projectInfo->getStatus()->getValue() === '') {
            $errors['status'] = 'status is required.';
        }

        if ($errors !== []) {
            throw (new ValidationException())
                ->setContext($errors)
            ;
        }
    }
}
