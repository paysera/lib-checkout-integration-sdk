<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk;

use Nyholm\Psr7\Factory\Psr17Factory;
use Paysera\CheckoutSdk\Entity\PaymentApiEnvironment;
use Paysera\CheckoutSdk\Service\Callback\CallbackHandlerRegistry;
use Paysera\CheckoutSdk\Service\Callback\Handler\OrderAmountPaidCallbackHandler;
use Paysera\CheckoutSdk\Service\Factory\HttpClientFactory;
use Paysera\CheckoutSdk\Repository\PaymentApiAuthTokenRepositoryInterface;
use Paysera\CheckoutSdk\Repository\InMemoryPaymentApiAuthTokenRepository;
use Paysera\CheckoutSdk\Repository\PaymentApiCredentialsRepositoryInterface;
use Paysera\CheckoutSdk\Repository\InMemoryPaymentApiCredentialsRepository;
use Paysera\CheckoutSdk\Service\Http\Plugin\ApiClientFormatterInterface;
use Paysera\CheckoutSdk\Service\Http\Plugin\FormUrlencodedApiClientBodyFormatter;
use Paysera\CheckoutSdk\Service\Http\Plugin\JsonApiClientBodyFormatter;
use Paysera\CheckoutSdk\Service\Http\Plugin\SecureApiClientFormatter;
use Paysera\CheckoutSdk\Service\Factory\PluginClientFactory;
use Paysera\CheckoutSdk\Service\JWT\JwtBridge;
use Paysera\CheckoutSdk\Service\JWT\JwtDecoder;
use Paysera\CheckoutSdk\Service\JWT\PaymentApiJwtDecoderInterface;
use Paysera\CheckoutSdk\Service\JWT\PaymentApiJwtValidatorInterface;
use Paysera\CheckoutSdk\Service\PaymentApiConfiguration;
use Paysera\CheckoutSdk\Service\Provider\MerchantAreaUrlProvider;
use Paysera\CheckoutSdk\Service\Provider\PaymentApiUrlProvider;
use Paysera\CheckoutSdk\Util\Cache\InMemoryCache;
use Paysera\CheckoutSdk\Util\Clock;
use Paysera\CheckoutSdk\Util\Container;
use Paysera\CheckoutSdk\Util\SleeperInterface;
use Paysera\CheckoutSdk\Util\UsleepSleeper;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Clock\ClockInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class SdkFacadeBuilder
{
    private LoggerInterface $logger;
    private HttpClientFactory $httpClientFactory;
    private ?ClientInterface $client;
    private PaymentApiAuthTokenRepositoryInterface $authTokenRepository;
    private PaymentApiCredentialsRepositoryInterface $apiCredentialsRepository;
    private PaymentApiEnvironment $paymentApiEnvironment;
    private ApiClientFormatterInterface $apiClientFormatter;
    private ClockInterface $clock;
    private CacheItemPoolInterface $cacheItemPool;
    private int $jwtLeewaySeconds;

    public function __construct()
    {
        $this->client = null;
        $this->logger = new NullLogger();
        $this->httpClientFactory = new HttpClientFactory();
        $this->authTokenRepository = new InMemoryPaymentApiAuthTokenRepository();
        $this->apiCredentialsRepository = new InMemoryPaymentApiCredentialsRepository();
        $this->paymentApiEnvironment = new PaymentApiEnvironment();
        $this->apiClientFormatter = (new SecureApiClientFormatter(true, true))
            ->addCustomBodyFormatter(new JsonApiClientBodyFormatter())
            ->addCustomBodyFormatter(new FormUrlencodedApiClientBodyFormatter())
        ;
        $this->clock = new Clock();
        $this->cacheItemPool = new InMemoryCache($this->clock);
        $this->jwtLeewaySeconds = JwtBridge::DEFAULT_LEEWAY_SECONDS;
    }

    public function build(): SdkFacade
    {
        $httpClient = $this->client ?? $this->httpClientFactory->create();
        $pluginClient = (new PluginClientFactory($httpClient, $this->logger, $this->apiClientFormatter))
            ->create()
        ;
        $apiConfiguration = new PaymentApiConfiguration($this->paymentApiEnvironment);

        $container = new Container();
        $container->set(LoggerInterface::class, $this->logger);
        $container->set(ClientInterface::class, $pluginClient);
        $container->set(PaymentApiConfiguration::class, $apiConfiguration);
        $container->set(PaymentApiAuthTokenRepositoryInterface::class, $this->authTokenRepository);
        $container->set(PaymentApiCredentialsRepositoryInterface::class, $this->apiCredentialsRepository);
        $container->set(ApiClientFormatterInterface::class, $this->apiClientFormatter);
        $container->set(ClockInterface::class, $this->clock);
        $container->set(CacheItemPoolInterface::class, $this->cacheItemPool);

        $container->setDefinition(RequestFactoryInterface::class)
            ->setAlias(Psr17Factory::class)
        ;
        $container->setDefinition(StreamFactoryInterface::class)
            ->setAlias(Psr17Factory::class)
        ;
        $container->setDefinition(PaymentApiJwtDecoderInterface::class)
            ->setAlias(JwtDecoder::class)
        ;
        $container->setDefinition(PaymentApiJwtValidatorInterface::class)
            ->setAlias(JwtDecoder::class)
        ;
        $container->setDefinition(SleeperInterface::class)
            ->setAlias(UsleepSleeper::class)
        ;
        $container->setDefinition(PaymentApiUrlProvider::class)
            ->setEnv('PAYSERA_CHECKOUT_SDK_PAYMENT_API_PRODUCTION_BASE_URL', 'productionBaseUrl')
            ->setEnv('PAYSERA_CHECKOUT_SDK_PAYMENT_API_SANDBOX_BASE_URL', 'sandboxBaseUrl')
        ;
        $container->setDefinition(JwtBridge::class)
            ->setArgument('leewaySeconds', $this->jwtLeewaySeconds)
        ;
        $container->setDefinition(MerchantAreaUrlProvider::class)
            ->setEnv('PAYSERA_CHECKOUT_SDK_MERCHANT_AREA_PRODUCTION_BASE_URL', 'productionBaseUrl')
            ->setEnv('PAYSERA_CHECKOUT_SDK_MERCHANT_AREA_SANDBOX_BASE_URL', 'sandboxBaseUrl')
        ;
        $container->setDefinition(CallbackHandlerRegistry::class)
            ->setArgument('handlers', [
                $container->get(OrderAmountPaidCallbackHandler::class),
            ])
        ;

        return $container->get(SdkFacade::class);
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function setHttpClient(ClientInterface $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function setPaymentApiAuthTokenRepository(PaymentApiAuthTokenRepositoryInterface $authTokenRepository): self
    {
        $this->authTokenRepository = $authTokenRepository;

        return $this;
    }

    public function setPaymentApiCredentialsRepository(PaymentApiCredentialsRepositoryInterface $apiCredentialsRepository): self
    {
        $this->apiCredentialsRepository = $apiCredentialsRepository;

        return $this;
    }

    public function setApiClientFormatter(ApiClientFormatterInterface $apiClientFormatter): self
    {
        $this->apiClientFormatter = $apiClientFormatter;

        return $this;
    }

    public function setClock(ClockInterface $clock): self
    {
        $this->clock = $clock;

        return $this;
    }

    public function setCacheItemPool(CacheItemPoolInterface $cacheItemPool): self
    {
        $this->cacheItemPool = $cacheItemPool;

        return $this;
    }

    /**
     * Clock-skew allowance (in seconds) for JWT time-claim validation
     * (iat, nbf, exp). Increase when merchant servers may have unsynchronised
     * clocks. Defaults to JwtBridge::DEFAULT_LEEWAY_SECONDS.
     */
    public function setJwtLeeway(int $leewaySeconds): self
    {
        $this->jwtLeewaySeconds = $leewaySeconds;

        return $this;
    }
}
