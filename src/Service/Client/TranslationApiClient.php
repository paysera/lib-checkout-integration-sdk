<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Client;

use Paysera\CheckoutSdk\Exception\BaseException;
use Paysera\CheckoutSdk\Exception\InvalidArgumentException;
use Paysera\CheckoutSdk\Exception\ValidationException;
use Paysera\CheckoutSdk\Service\Client\Factory\ApiClientFactory;
use Paysera\CheckoutSdk\Service\Serializer\JsonSerializer;
use Paysera\CheckoutSdk\Service\Validator\TranslationsValidator;
use Paysera\CheckoutSdk\Util\MessagePayloadExtractor;
use Psr\Cache\CacheItemPoolInterface;

class TranslationApiClient
{
    private const SDK_NAMESPACE = 'lib_checkout_integration_sdk';
    private const TRANSLATION_PROXY_URL = 'https://translation-proxy.paysera.com/public/%s/translations.json';
    private const PLUGIN_NAMESPACE_PATTERN = '/^\w+$/';
    private const CACHE_KEY_PREFIX = 'paysera_sdk_translation';
    private const DEFAULT_CACHE_TTL = 24 * 60 * 60;

    private ApiClientFactory $apiClientFactory;
    private JsonSerializer $jsonSerializer;
    private MessagePayloadExtractor $messagePayloadExtractor;
    private TranslationsValidator $translationDataValidator;
    private CacheItemPoolInterface $cache;
    private int $cacheTtlSeconds;

    public function __construct(
        ApiClientFactory $apiClientFactory,
        JsonSerializer $jsonSerializer,
        MessagePayloadExtractor $messagePayloadExtractor,
        TranslationsValidator $translationDataValidator,
        CacheItemPoolInterface $cache,
        ?int $cacheTtlSeconds = null
    ) {
        $this->apiClientFactory = $apiClientFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->messagePayloadExtractor = $messagePayloadExtractor;
        $this->translationDataValidator = $translationDataValidator;
        $this->cache = $cache;
        $this->cacheTtlSeconds = $cacheTtlSeconds ?? self::DEFAULT_CACHE_TTL;
    }

    public function getTranslationsUrl(string $namespace): string
    {
        return sprintf(self::TRANSLATION_PROXY_URL, $namespace);
    }

    /**
     * @throws ValidationException
     * @throws InvalidArgumentException
     */
    public function isDataCached(?string $pluginNamespace = null): bool
    {
        $this->validatePluginNamespace($pluginNamespace);

        return $this->cache->hasItem($this->buildCacheKey($pluginNamespace));
    }

    /**
     * @throws ValidationException
     * @throws InvalidArgumentException
     */
    public function invalidateDataCache(?string $pluginNamespace = null): void
    {
        $this->validatePluginNamespace($pluginNamespace);

        $this->cache->deleteItem($this->buildCacheKey($pluginNamespace));
    }

    /**
     * @throws BaseException
     */
    public function getTranslations(?string $pluginNamespace = null): array
    {
        $this->validatePluginNamespace($pluginNamespace);

        $item = $this->cache->getItem($this->buildCacheKey($pluginNamespace));
        if ($item->isHit()) {
            return $item->get();
        }

        $sdkTranslations = $this->fetchTranslations(self::SDK_NAMESPACE);
        $translations = $pluginNamespace === null
            ? $sdkTranslations
            : $this->mergeTranslations($sdkTranslations, $this->fetchTranslations($pluginNamespace));

        $item->set($translations)->expiresAfter($this->cacheTtlSeconds);
        $this->cache->save($item);

        return $translations;
    }

    /**
     * @throws BaseException
     */
    private function fetchTranslations(string $namespace): array
    {
        $apiClient = $this->apiClientFactory->createApiClient();

        $response = $apiClient->sendGetRequest($this->getTranslationsUrl($namespace));

        $responsePayload = $this->messagePayloadExtractor->getPayload($response);
        $responseData = $this->jsonSerializer->deserialize($responsePayload);

        $this->translationDataValidator->validate($responseData);

        return $responseData;
    }

    private function mergeTranslations(array $base, array $override): array
    {
        return array_replace_recursive($base, $override);
    }

    private function buildCacheKey(?string $pluginNamespace): string
    {
        return $pluginNamespace === null
            ? self::CACHE_KEY_PREFIX
            : self::CACHE_KEY_PREFIX . '_' . $pluginNamespace;
    }

    /**
     * @throws ValidationException
     */
    private function validatePluginNamespace(?string $pluginNamespace): void
    {
        if ($pluginNamespace !== null && preg_match(self::PLUGIN_NAMESPACE_PATTERN, $pluginNamespace) !== 1) {
            throw (new ValidationException('Invalid plugin namespace format'))
                ->setContext(['pluginNamespace' => $pluginNamespace])
            ;
        }
    }
}
