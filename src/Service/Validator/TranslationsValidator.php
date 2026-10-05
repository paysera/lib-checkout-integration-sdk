<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator;

use Paysera\CheckoutSdk\Exception\ValidationException;

class TranslationsValidator
{
    /**
     * @throws ValidationException
     */
    public function validate(array $translations): void
    {
        foreach ($translations as $locale => $vocabularies) {
            if (!is_string($locale) || preg_match('/^[a-z]{2}$/', $locale) !== 1) {
                throw (new ValidationException())
                    ->setContext(['locale' => 'Locale is not valid. It must be a 2-letter string.'])
                ;
            }
            if (!is_array($vocabularies)) {
                throw (new ValidationException())
                    ->setContext(['vocabularies' => 'Each locale must have a list of vocabularies.'])
                ;
            }

            $this->validateVocabularies($vocabularies);
        }
    }

    /**
     * @throws ValidationException
     */
    private function validateVocabularies(array $vocabularies): void
    {
        foreach ($vocabularies as $vocabularyKey => $vocabularyValue) {
            if (!is_string($vocabularyKey) || $vocabularyKey === '') {
                throw (new ValidationException())
                    ->setContext(['vocabularyKey' => 'Vocabulary key must be a non-empty string.'])
                ;
            }
            if (!is_array($vocabularyValue)) {
                throw (new ValidationException())
                    ->setContext(['vocabulary' => 'Vocabulary value must be an array.'])
                ;
            }

            $this->validateVocabulary($vocabularyValue);
        }
    }

    /**
     * @throws ValidationException
     */
    private function validateVocabulary(array $vocabularyValue): void
    {
        foreach ($vocabularyValue as $messageKey => $messageTranslation) {
            if (!is_string($messageKey) || $messageKey === '') {
                throw (new ValidationException())
                    ->setContext(['messageKey' => 'Message key must be a non-empty string.'])
                ;
            }
            if (!is_string($messageTranslation)) {
                throw (new ValidationException())
                    ->setContext(['messageTranslation' => 'Message translation must be a string.'])
                ;
            }
        }
    }
}
