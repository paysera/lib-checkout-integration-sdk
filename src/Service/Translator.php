<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service;

class Translator
{
    private const VOCABULARY_JSON = 'messages.json';
    private const VOCABULARY_PHP = 'messages.php';

    private array $translations;

    public function __construct(array $translations)
    {
        $this->translations = $translations;
    }

    public function getTranslations(?string $locale = null, ?string $vocabulary = null): array
    {
        if ($locale === null) {
            return $this->translations;
        }

        if ($vocabulary !== null) {
            return $this->translations[$locale][$vocabulary] ?? [];
        }

        return $this->translations[$locale][self::VOCABULARY_JSON]
            ?? $this->translations[$locale][self::VOCABULARY_PHP]
            ?? [];
    }

    public function translate(string $key, ?string $locale = null, ?string $vocabulary = null): ?string
    {
        $selectedLocale = $locale ?? 'en';

        $messages = $this->getTranslations($selectedLocale, $vocabulary);

        return $messages[$key] ?? null;
    }
}
