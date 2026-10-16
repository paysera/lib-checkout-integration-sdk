<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Client;

use Paysera\CheckoutSdk\Exception\ApiClientException;
use Paysera\CheckoutSdk\Util\SleeperInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class HttpRetryExecutor
{
    public const DEFAULT_MAX_RETRIES = 1;
    public const DEFAULT_DELAY_MS = 1000;

    private const HTTP_REQUEST_TIMEOUT = 408;
    private const HTTP_TOO_MANY_REQUESTS = 429;
    private const HTTP_SERVER_ERROR_MIN = 500;

    private SleeperInterface $sleeper;
    private LoggerInterface $logger;

    public function __construct(SleeperInterface $sleeper, LoggerInterface $logger)
    {
        $this->sleeper = $sleeper;
        $this->logger = $logger;
    }

    /**
     * Executes the operation, retrying transient API failures.
     *
     * Retries only on conditions that may recover on a second attempt:
     * 5xx server errors, 408 Request Timeout, 429 Too Many Requests, and
     * transport-level failures (no HTTP status, surfaced as the
     * ApiClientException default code). Other 4xx responses (including 401)
     * are deterministic client errors and are rethrown immediately. Token
     * refresh on 401 is owned by PaymentApiClient::executeWithTokenRetry().
     *
     * @param callable(): ResponseInterface $operation
     *
     * @throws ApiClientException
     */
    public function execute(
        callable $operation,
        int $maxRetries = self::DEFAULT_MAX_RETRIES,
        int $delayMs = self::DEFAULT_DELAY_MS
    ): ResponseInterface {
        $attempts = 0;

        while (true) {
            try {
                return $operation();
            } catch (ApiClientException $exception) {
                if (!$this->isRetryable($exception->getCode())) {
                    throw $exception;
                }

                $attempts++;

                if ($attempts > $maxRetries) {
                    throw $exception;
                }

                $this->logger->warning(
                    'API request failed (status {status_code}): {error} ({exception_class}), retrying in {delay_ms}ms (attempt {attempt}/{max_retries})',
                    [
                        'attempt' => $attempts,
                        'max_retries' => $maxRetries,
                        'delay_ms' => $delayMs,
                        'status_code' => $exception->getCode(),
                        'error' => $exception->getMessage(),
                        'exception_class' => get_class($exception),
                    ]
                );

                $this->sleeper->sleep($delayMs);
            }
        }
    }

    private function isRetryable(int $code): bool
    {
        if ($code === ApiClientException::E_API_CLIENT) {
            return true;
        }

        if ($code >= self::HTTP_SERVER_ERROR_MIN) {
            return true;
        }

        return $code === self::HTTP_REQUEST_TIMEOUT
            || $code === self::HTTP_TOO_MANY_REQUESTS;
    }
}
