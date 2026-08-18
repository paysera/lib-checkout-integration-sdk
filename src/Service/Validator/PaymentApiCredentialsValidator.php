<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator;

use Paysera\CheckoutSdk\Entity\PaymentApiCredentials;
use Paysera\CheckoutSdk\Exception\ValidationException;

class PaymentApiCredentialsValidator
{
    /**
     * @throws ValidationException
     */
    public function validate(PaymentApiCredentials $apiCredentials): void
    {
        $errors = [];

        if (trim($apiCredentials->getClientId()) === '') {
            $errors['clientId'] = 'Client ID is required.';
        }

        if (trim($apiCredentials->getClientSecret()) === '') {
            $errors['clientSecret'] = 'Client secret is required.';
        }

        if ($errors !== []) {
            throw (new ValidationException())
                ->setContext($errors)
            ;
        }
    }
}
