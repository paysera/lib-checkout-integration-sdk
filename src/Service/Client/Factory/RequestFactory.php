<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Client\Factory;

use Nyholm\Psr7\Uri;
use Paysera\CheckoutSdk\Exception\RequestFactoryException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class RequestFactory
{
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    private LoggerInterface $logger;

    public function __construct(
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        LoggerInterface $logger
    ) {
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->logger = $logger;
    }

    /**
     * @throws RequestFactoryException
     */
    public function createRequest(
        string $method,
        string $url,
        ?array $query = null,
        ?string $body = null,
        ?array $headers = null
    ): RequestInterface {
        try {
            $uri = new Uri($url);

            if ($query !== null && $query !== []) {
                $uri = $uri->withQuery(http_build_query($query));
            }

            $request = $this->requestFactory->createRequest($method, $uri);

            if ($body !== null && $body !== '') {
                $request = $request->withBody($this->streamFactory->createStream($body));
            }

            if ($headers !== null) {
                foreach ($headers as $name => $value) {
                    $request = $request->withHeader($name, $value);
                }
            }

            return $request;
        } catch (Throwable $exception) {
            $this->logger->error(
                'Failed to create request',
                [
                    'exception' => $exception,
                ]
            );

            throw new RequestFactoryException('Failed to create request', null, $exception);
        }
    }
}
