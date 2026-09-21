<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Facade;

use Paysera\CheckoutSdk\Entity\SupportContact;
use Paysera\CheckoutSdk\Service\Provider\CommonInformationProvider;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

class Infrastructure
{
    private ContainerInterface $container;
    private LoggerInterface $logger;
    private ClientInterface $httpClient;
    private CommonInformationProvider $commonInformationProvider;

    public function __construct(
        ContainerInterface $container,
        LoggerInterface $logger,
        ClientInterface $httpClient,
        CommonInformationProvider $commonInformationProvider
    ) {
        $this->container = $container;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->commonInformationProvider = $commonInformationProvider;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getHttpClient(): ClientInterface
    {
        return $this->httpClient;
    }

    public function getSupportContact(): SupportContact
    {
        return $this->commonInformationProvider->getSupportContact();
    }
}
