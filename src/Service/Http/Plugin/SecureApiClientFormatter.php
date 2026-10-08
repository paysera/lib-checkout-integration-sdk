<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Http\Plugin;

use Paysera\CheckoutSdk\Exception\RuntimeException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class SecureApiClientFormatter implements ApiClientFormatterInterface
{
    protected const REPLACEMENT = '[REDACTED]';
    protected const UNSUPPORTED_CONTENT_TYPE = '[UNSUPPORTED_CONTENT_TYPE]';
    protected const MAX_BODY_LENGTH = 2000;
    protected const SENSITIVE_PARAMETERS_DEFAULT = [
        'client_secret',
        'client_id',
        'access_token',
        'password',
        'token',
    ];
    protected const SENSITIVE_HEADERS_DEFAULT = [
        'authorization',
        'set-cookie',
    ];

    /** @var string[] */
    protected array $sensitiveParameters;

    /** @var string[] */
    protected array $sensitiveHeaders;
    protected bool $includeBody;
    protected bool $includeHeaders;
    /**
     * @var ApiClientBodyFormatterInterface[]
     */
    protected array $customBodyFormatters;

    /**
     * Built-in formatters, always available so that a directly constructed
     * formatter redacts without external wiring.
     *
     * @var ApiClientBodyFormatterInterface[]
     */
    protected array $defaultBodyFormatters;

    /**
     * @param string[] $sensitiveParameters
     * @param string[] $sensitiveHeaders
     */
    public function __construct(
        bool $includeHeaders = false,
        bool $includeBody = false,
        array $sensitiveParameters = self::SENSITIVE_PARAMETERS_DEFAULT,
        array $sensitiveHeaders = self::SENSITIVE_HEADERS_DEFAULT
    ) {
        $this->includeHeaders = $includeHeaders;
        $this->includeBody = $includeBody;
        $this->customBodyFormatters = [];
        $this->defaultBodyFormatters = [
            new JsonApiClientBodyFormatter(),
            new FormUrlencodedApiClientBodyFormatter(),
        ];
        $this->sensitiveParameters = array_values(
            array_merge(self::SENSITIVE_PARAMETERS_DEFAULT, array_unique($sensitiveParameters))
        );
        $this->sensitiveHeaders = array_values(
            array_merge(
                self::SENSITIVE_HEADERS_DEFAULT,
                array_unique(array_map('strtolower', $sensitiveHeaders))
            )
        );
    }

    public function addCustomBodyFormatter(ApiClientBodyFormatterInterface $bodyFormatter): self
    {
        $this->customBodyFormatters[] = $bodyFormatter;

        return $this;
    }

    public function formatRequest(RequestInterface $request): string
    {
        $line = sprintf(
            '%s %s',
            $request->getMethod(),
            $this->sanitizeUri($request->getUri())
        );

        return $line
            . $this->formatMessageHeaders($request)
            . $this->formatMessageBody($request);
    }

    public function formatResponse(ResponseInterface $response): string
    {
        return $this->formatResponseMessage($response);
    }

    public function formatResponseForRequest(
        ResponseInterface $response,
        RequestInterface $request
    ): string {
        return $this->formatResponseMessage($response);
    }

    private function formatResponseMessage(ResponseInterface $response): string
    {
        $line = sprintf(
            'HTTP/%s %d %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );

        return $line
            . $this->formatMessageHeaders($response)
            . $this->formatMessageBody($response);
    }

    /**
     * Main hook for work with body.
     */
    protected function formatBody(string $body, ?string $contentType): string
    {
        if ($body === '') {
            return '';
        }

        foreach ($this->getBodyFormatters() as $formatter) {
            if ($formatter->canFormatBody($contentType ?? '')) {
                try {
                    $result = $formatter->formatBody($body, $this->sensitiveParameters, self::REPLACEMENT);
                } catch (RuntimeException $exception) {
                    return '[FORMATTING_FAILED]';
                }

                return $this->truncate($result);
            }
        }

        // Fail closed: a body no formatter claims cannot be redacted, so it is
        // never logged. Returning it raw would leak credentials for any content
        // type outside the known set.
        return self::UNSUPPORTED_CONTENT_TYPE;
    }

    /**
     * Custom formatters take precedence, so an integrator can override how a
     * content type the SDK already knows is redacted.
     *
     * @return ApiClientBodyFormatterInterface[]
     */
    protected function getBodyFormatters(): array
    {
        return array_merge($this->customBodyFormatters, $this->defaultBodyFormatters);
    }

    private function sanitizeUri(UriInterface $uri): UriInterface
    {
        $query = $uri->getQuery();

        if ($query === '') {
            return $uri;
        }

        parse_str($query, $params);

        foreach ($this->sensitiveParameters as $param) {
            if (array_key_exists($param, $params)) {
                $params[$param] = self::REPLACEMENT;
            }
        }

        $newQuery = http_build_query($params);

        return $uri->withQuery($newQuery);
    }

    /**
     * @param array<string, string[]> $headers
     * @return array<string, string[]>
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sanitized = [];

        foreach ($headers as $name => $values) {
            if (in_array(strtolower($name), $this->sensitiveHeaders, true)) {
                $sanitized[$name] = [static::REPLACEMENT];
            } else {
                $sanitized[$name] = $values;
            }
        }

        return $sanitized;
    }

    /**
     * @param array<string, string[]> $headers
     */
    private function formatHeaders(array $headers): string
    {
        $out = '';

        foreach ($headers as $name => $values) {
            $out .= sprintf(' [%s: %s]', $name, implode(', ', $values));
        }

        return $out;
    }

    protected function truncate(string $text): string
    {
        if (mb_strlen($text) <= self::MAX_BODY_LENGTH) {
            return $text;
        }

        return mb_substr($text, 0, self::MAX_BODY_LENGTH) . '...[TRUNCATED]';
    }

    private function formatMessageHeaders(MessageInterface $message): string
    {
        $result = '';

        if ($this->includeHeaders) {
            $headersPart = $this->formatHeaders(
                $this->sanitizeHeaders($message->getHeaders())
            );

            if ($headersPart !== '') {
                $result .= PHP_EOL
                    . 'Headers:'
                    . $headersPart;
            }
        }

        return rtrim($result);
    }

    private function formatMessageBody(MessageInterface $message): string
    {
        $result = '';

        if ($this->includeBody) {
            $bodyPart = $this->formatBody(
                (string)$message->getBody(),
                $message->getHeaderLine('Content-Type')
            );

            $stream = $message->getBody();
            if ($stream->isSeekable()) {
                $stream->rewind();
            }

            if ($bodyPart !== '') {
                $result .= PHP_EOL
                    . 'Body: '
                    . $bodyPart;
            }
        }

        return rtrim($result);
    }
}
