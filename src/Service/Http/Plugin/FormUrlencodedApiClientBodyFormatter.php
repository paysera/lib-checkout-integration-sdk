<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Http\Plugin;

class FormUrlencodedApiClientBodyFormatter implements ApiClientBodyFormatterInterface
{
    use MediaTypeParserTrait;

    /**
     * @param string[] $sensitiveParameters
     */
    public function formatBody(string $body, array $sensitiveParameters, string $replacement): string
    {
        if ($body === '') {
            return '';
        }

        parse_str($body, $params);

        foreach ($sensitiveParameters as $key) {
            if (array_key_exists($key, $params)) {
                $params[$key] = $replacement;
            }
        }

        return http_build_query($params);
    }

    public function canFormatBody(string $contentType): bool
    {
        return $this->parseMediaType($contentType) === 'application/x-www-form-urlencoded';
    }
}
