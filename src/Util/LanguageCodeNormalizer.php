<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Util;

class LanguageCodeNormalizer
{
    public static function normalize(?string $locale): ?string
    {
        if ($locale === null) {
            return null;
        }

        $code = strtolower(substr(trim($locale), 0, 2));

        if (preg_match('/^[a-z]{2}$/', $code) !== 1) {
            return null;
        }

        $rest = substr(trim($locale), 2);
        if ($rest !== '' && preg_match('/^[_-][A-Za-z0-9]+([_-][A-Za-z0-9]+)*$/', $rest) !== 1) {
            return null;
        }

        return $code;
    }
}
