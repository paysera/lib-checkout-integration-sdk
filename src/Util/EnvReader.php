<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Util;

class EnvReader
{
    public static function getAsString(string $key, ?string $default = null): ?string
    {
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return (string)$_ENV[$key];
        }

        $envValue = getenv($key);

        if ($envValue !== false && $envValue !== '') {
            return (string)$envValue;
        }

        return $default;
    }
}
