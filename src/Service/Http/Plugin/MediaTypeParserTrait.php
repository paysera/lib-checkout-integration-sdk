<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Http\Plugin;

trait MediaTypeParserTrait
{
    /**
     * Reduces a Content-Type header line to its bare media type.
     *
     * PSR-7 joins a repeated header into one comma-separated line, so the first
     * value is taken before parameters are dropped. Media types are
     * case-insensitive per RFC 2045, hence the lowercasing.
     */
    protected function parseMediaType(string $contentType): string
    {
        $firstValue = explode(',', $contentType)[0];

        return strtolower(trim(explode(';', $firstValue)[0]));
    }
}
