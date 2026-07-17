<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Facade;

use Paysera\CheckoutSdk\Exception\BaseException;
use Paysera\CheckoutSdk\Service\Client\TranslationApiClient;
use Paysera\CheckoutSdk\Service\Translator;
use Psr\Log\LoggerInterface;

class Translations
{
    private LoggerInterface $logger;
    private TranslationApiClient $translationApiClient;

    public function __construct(
        LoggerInterface $logger,
        TranslationApiClient $translationApiClient
    ) {
        $this->logger = $logger;
        $this->translationApiClient = $translationApiClient;
    }

    public function getTranslationsUrl(string $namespace): string
    {
        return $this->translationApiClient->getTranslationsUrl($namespace);
    }

    public function isDataCached(?string $pluginNamespace = null): bool
    {
        return $this->translationApiClient->isDataCached($pluginNamespace);
    }

    public function invalidateDataCache(?string $pluginNamespace = null): void
    {
        $this->translationApiClient->invalidateDataCache($pluginNamespace);
    }

    public function getTranslator(?string $pluginNamespace = null): Translator
    {
        try {
            $translations = $this->translationApiClient->getTranslations($pluginNamespace);

            return new Translator($translations);
        } catch (BaseException $exception) {
            $this->logger->error(
                'Translations request failed',
                [
                    'exception' => $exception,
                ]
            );

            return new Translator([]);
        }
    }
}
