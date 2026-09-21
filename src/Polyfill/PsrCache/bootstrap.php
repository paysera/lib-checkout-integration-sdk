<?php

declare(strict_types=1);

use Composer\InstalledVersions;
use Paysera\CheckoutSdk\Polyfill\PsrCache\CacheItem1x;
use Paysera\CheckoutSdk\Polyfill\PsrCache\CacheItem3x;
use Paysera\CheckoutSdk\Util\Cache\CacheItem;

$psrCacheVersion = InstalledVersions::getPrettyVersion('psr/cache');

if (!class_exists(CacheItem::class, false)) {
    if (version_compare($psrCacheVersion, '3.0', '>=')) {
        // psr/cache 3.x - interface has static return types
        class_alias(CacheItem3x::class, CacheItem::class);
    } else {
        // psr/cache 1.x/2.x - interface has NO return types
        class_alias(CacheItem1x::class, CacheItem::class);
    }
}
