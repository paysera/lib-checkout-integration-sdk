<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Http\Plugin;

use Paysera\CheckoutSdk\Exception\RuntimeException;

interface ApiClientBodyFormatterInterface
{
    /**
     * @param string[] $sensitiveParameters
     * @throws RuntimeException
     */
    public function formatBody(string $body, array $sensitiveParameters, string $replacement): string;

    public function canFormatBody(string $contentType): bool;
}
