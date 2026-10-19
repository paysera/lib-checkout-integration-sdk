<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Factory;

use Http\Client\Common\Plugin\ContentLengthPlugin;
use Http\Client\Common\Plugin\ContentTypePlugin;
use Http\Client\Common\Plugin\LoggerPlugin;
use Http\Client\Common\PluginClient;
use Paysera\CheckoutSdk\Service\Http\Plugin\ApiClientFormatterInterface;
use Psr\Log\LoggerInterface;
use Psr\Http\Client\ClientInterface;

class PluginClientFactory
{
    private ClientInterface $httpClient;
    private LoggerInterface $logger;
    private ApiClientFormatterInterface $formatter;

    public function __construct(
        ClientInterface $httpClient,
        LoggerInterface $logger,
        ApiClientFormatterInterface $formatter
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->formatter = $formatter;
    }

    public function create(): ClientInterface
    {
        $plugins = [
            new ContentTypePlugin(),
            new ContentLengthPlugin(),
            new LoggerPlugin($this->logger, $this->formatter),
        ];

        return new PluginClient($this->httpClient, $plugins);
    }
}
