<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator;

use Paysera\CheckoutSdk\Exception\ApiClientException;
use Psr\Http\Message\ResponseInterface;

class ResponseValidator
{
    private const HTTP_SUCCESS_MIN = 200;
    private const HTTP_SUCCESS_MAX = 299;

    /**
     * @throws ApiClientException
     */
    public function validate(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode >= self::HTTP_SUCCESS_MIN && $statusCode <= self::HTTP_SUCCESS_MAX) {
            return;
        }

        $responseBody = (string) $response->getBody();
        $errorData = $this->parseErrorResponse($responseBody);
        $message = $this->buildErrorMessage($statusCode, $response->getReasonPhrase(), $errorData);

        $exception = new ApiClientException($message, $statusCode);

        if ($responseBody !== '') {
            $exception->setContext($responseBody);
        }

        throw $exception;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseErrorResponse(string $responseBody): array
    {
        if ($responseBody === '') {
            return [];
        }

        $decoded = json_decode($responseBody, true);

        if (!is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    /**
     * @param array<string, mixed> $errorData
     */
    private function buildErrorMessage(int $statusCode, string $reasonPhrase, array $errorData): string
    {
        $error = $this->extractString($errorData, 'error');
        $errorDescription = $this->extractString($errorData, 'error_description');

        if ($error === null && $errorDescription === null) {
            return sprintf('HTTP %d: %s', $statusCode, $reasonPhrase);
        }

        $message = sprintf('HTTP %d', $statusCode);

        if ($error !== null) {
            $message .= sprintf(' [%s]', $error);
        }

        if ($errorDescription !== null) {
            $message .= sprintf(': %s', $errorDescription);
        }

        $errorProperties = $this->extractErrorProperties($errorData);
        if ($errorProperties !== '') {
            $message .= sprintf(' (%s)', $errorProperties);
        }

        return $message;
    }

    /**
     * @param array<string, mixed> $errorData
     */
    private function extractString(array $errorData, string $key): ?string
    {
        if (!isset($errorData[$key])) {
            return null;
        }

        return is_string($errorData[$key]) ? $errorData[$key] : null;
    }

    /**
     * @param array<string, mixed> $errorData
     */
    private function extractErrorProperties(array $errorData): string
    {
        if (!isset($errorData['error_properties']) || !is_array($errorData['error_properties'])) {
            return '';
        }

        $formatted = [];

        foreach ($errorData['error_properties'] as $field => $errors) {
            if (!is_string($field) || !is_array($errors)) {
                continue;
            }

            $stringErrors = array_filter($errors, 'is_string');
            if (count($stringErrors) > 0) {
                $formatted[] = sprintf('%s: %s', $field, implode(', ', $stringErrors));
            }
        }

        return implode('; ', $formatted);
    }
}
