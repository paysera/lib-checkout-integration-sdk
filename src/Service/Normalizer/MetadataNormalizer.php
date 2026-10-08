<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer;

use Paysera\CheckoutSdk\Entity\Metadata;

class MetadataNormalizer
{
    public function normalize(Metadata $metadata): array
    {
        $data = [
            'referer' => $metadata->getReferer(),
            'platform' => $metadata->getPlatform(),
            'platform_version' => $metadata->getPlatformVersion(),
            'plugin_name' => $metadata->getPluginName(),
            'plugin_version' => $metadata->getPluginVersion(),
            'php_version' => $metadata->getPhpVersion(),
        ];

        return array_merge($data, $metadata->getCustomFields());
    }

    public function denormalize(array $data): Metadata
    {
        $referer = $data['referer'] ?? '';
        $platform = $data['platform'] ?? null;
        $platformVersion = $data['platform_version'] ?? null;
        $pluginName = $data['plugin_name'] ?? null;
        $pluginVersion = $data['plugin_version'] ?? null;

        $customFields = $data;
        unset(
            $customFields['referer'],
            $customFields['platform'],
            $customFields['platform_version'],
            $customFields['plugin_name'],
            $customFields['plugin_version']
        );

        return new Metadata(
            $referer,
            $platform,
            $platformVersion,
            $pluginName,
            $pluginVersion,
            $customFields
        );
    }
}
