<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator\Common;

class ErrorBag
{
    private array $errors;

    public function __construct(array $errors = [])
    {
        $this->errors = $errors;
    }

    public function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
