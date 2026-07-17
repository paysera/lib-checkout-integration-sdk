<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator\Common;

use Paysera\CheckoutSdk\Exception\ValidationException;

class StringValidator
{
    private ValidatorExceptionHandler $validatorExceptionHandler;

    public function __construct(ValidatorExceptionHandler $validatorExceptionHandler)
    {
        $this->validatorExceptionHandler = $validatorExceptionHandler;
    }

    /**
     * @throws ValidationException
     */
    public function validateEmpty(
        string $string,
        string $fieldName,
        ?ValidatorErrorHandlerInterface $errorHandler = null
    ): void {
        if (trim($string) !== '') {
            return;
        }
        $handler = $errorHandler ?? $this->validatorExceptionHandler;

        $handler->handle(new ErrorBag([$fieldName => "$fieldName is required."]));
    }

    /**
     * @throws ValidationException
     */
    public function validateMaxLength(
        string $string,
        string $fieldName,
        int $maxLength,
        ?ValidatorErrorHandlerInterface $errorHandler = null
    ): void {
        if (strlen($string) <= $maxLength) {
            return;
        }

        $handler = $errorHandler ?? $this->validatorExceptionHandler;

        $handler->handle(new ErrorBag([$fieldName => "$fieldName cannot be longer than $maxLength characters."]));
    }

    /**
     * @throws ValidationException
     */
    public function validateUuid(
        string $uuid,
        string $fieldName,
        ?ValidatorErrorHandlerInterface $errorHandler = null
    ): void {
        $handler = $errorHandler ?? $this->validatorExceptionHandler;

        if ((bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid) === false) {
            $handler->handle(new ErrorBag([$fieldName => "$fieldName must be a valid UUID."]));
        }
    }

    /**
     * @throws ValidationException
     */
    public function validateUrl(
        string $url,
        string $fieldName,
        ?ValidatorErrorHandlerInterface $errorHandler = null
    ): void {
        if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
            $scheme = parse_url($url, PHP_URL_SCHEME);
            if ($scheme === 'https') {
                return;
            }
        }

        $handler = $errorHandler ?? $this->validatorExceptionHandler;

        $handler->handle(new ErrorBag([$fieldName => "$fieldName must be a valid URL."]));
    }

    /**
     * @throws ValidationException
     */
    public function validateEmail(
        string $email,
        string $fieldName,
        ?ValidatorErrorHandlerInterface $errorHandler = null
    ): void {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            return;
        }

        $handler = $errorHandler ?? $this->validatorExceptionHandler;

        $handler->handle(new ErrorBag([$fieldName => "$fieldName must be a valid email address."]));
    }
}
