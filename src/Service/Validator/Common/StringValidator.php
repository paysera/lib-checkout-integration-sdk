<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator\Common;

use Paysera\CheckoutSdk\Exception\ValidationException;

class StringValidator
{
    /**
     * SWIFT "X" character set plus underscore, kept in sync with
     * Reference.ALLOWED_CHARSET_REGEX in app-merchant-order. The trailing hyphen must stay last
     * so it is read as a literal inside the character class.
     */
    public const REFERENCE_ALLOWED_CHARACTERS = "A-Za-z0-9 _/?:().,'+-";

    private const REFERENCE_CHARSET_PATTERN = '#^[' . self::REFERENCE_ALLOWED_CHARACTERS . ']*$#D';

    private const ENCODING = 'UTF-8';

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
        // Counted in characters, not bytes: the API applies its limits to characters, so a
        // byte-based check rejects multibyte values the API would have accepted.
        if (mb_strlen($string, self::ENCODING) <= $maxLength) {
            return;
        }

        $handler = $errorHandler ?? $this->validatorExceptionHandler;

        $handler->handle(new ErrorBag([$fieldName => "$fieldName cannot be longer than $maxLength characters."]));
    }

    /**
     * Mirrors the character set the API accepts for order references: the SWIFT "X" set plus
     * underscore. Validating it here turns an HTTP 400 from the API into a local, field-level
     * error before the request is sent.
     *
     * @throws ValidationException
     */
    public function validateReferenceCharset(
        string $reference,
        string $fieldName,
        ?ValidatorErrorHandlerInterface $errorHandler = null
    ): void {
        if (preg_match(self::REFERENCE_CHARSET_PATTERN, $reference) === 1) {
            return;
        }

        $handler = $errorHandler ?? $this->validatorExceptionHandler;

        $handler->handle(new ErrorBag([
            $fieldName => sprintf(
                '%s may only contain the characters %s (the space is allowed).',
                $fieldName,
                self::REFERENCE_ALLOWED_CHARACTERS
            ),
        ]));
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
