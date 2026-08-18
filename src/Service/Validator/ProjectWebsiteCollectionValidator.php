<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator;

use Paysera\CheckoutSdk\Entity\Collection\ProjectWebsiteCollection;
use Paysera\CheckoutSdk\Exception\ValidationException;

class ProjectWebsiteCollectionValidator
{
    /**
     * @throws ValidationException
     */
    public function validate(ProjectWebsiteCollection $collection): void
    {
        $errors = [];

        foreach ($collection as $index => $website) {
            $url = $website->getUrl();

            if ($url === '') {
                $errors[sprintf('items.%d.url', $index)] = 'url is required.';
                continue;
            }

            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                $errors[sprintf('items.%d.url', $index)] = 'url must be a valid URL.';
            }
        }

        if ($errors !== []) {
            throw (new ValidationException())
                ->setContext($errors)
            ;
        }
    }
}
