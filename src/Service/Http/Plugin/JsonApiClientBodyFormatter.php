<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Http\Plugin;

use JsonException;
use Paysera\CheckoutSdk\Exception\RuntimeException;

class JsonApiClientBodyFormatter implements ApiClientBodyFormatterInterface
{
    /**
     * @param string[] $sensitiveParameters
     * @throws RuntimeException
     */
    public function formatBody(string $body, array $sensitiveParameters, string $replacement): string
    {
        if ($body === '') {
            return '';
        }

        try {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($decoded)) {
                throw new RuntimeException('Cannot sanitize scalar JSON body');
            }

            $sanitizedDecoded = $this->sanitizeDecodedBody($decoded, $sensitiveParameters, $replacement);

            return json_encode($sanitizedDecoded, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $exception) {
            throw new RuntimeException('Failed to format JSON body: ' . $exception->getMessage(), null, $exception);
        }
    }

    /**
     * @param array<string,mixed> $data
     * @param string[] $sensitiveParameters
     * @return array<string,mixed>
     */
    protected function sanitizeDecodedBody(array $data, array $sensitiveParameters, string $replacement): array
    {
        foreach ($data as $key => &$value) {
            if (in_array($key, $sensitiveParameters, true)) {
                $value = $replacement;
                continue;
            }

            if (is_array($value)) {
                $value = $this->sanitizeDecodedBody($value, $sensitiveParameters, $replacement);
            }
        }
        unset($value);

        return $data;
    }

    public function canFormatBody(string $contentType): bool
    {
        return str_contains($contentType, 'application/json');
    }
}
