<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

class Metadata
{
    private string $referer;
    private ?string $platform;
    private ?string $platformVersion;
    private ?string $pluginName;
    private ?string $pluginVersion;
    private array $customFields;
    private string $phpVersion;

    public function __construct(
        string $referer,
        ?string $platform = null,
        ?string $platformVersion = null,
        ?string $pluginName = null,
        ?string $pluginVersion = null,
        ?array $customFields = null
    ) {
        $this->referer = $referer;
        $this->platform = $platform;
        $this->platformVersion = $platformVersion;
        $this->pluginName = $pluginName;
        $this->pluginVersion = $pluginVersion;
        $this->customFields = $customFields ?? [];
        $this->phpVersion = PHP_VERSION;
    }

    public function getReferer(): string
    {
        return $this->referer;
    }

    public function getPlatform(): ?string
    {
        return $this->platform;
    }

    public function getPlatformVersion(): ?string
    {
        return $this->platformVersion;
    }

    public function getPluginName(): ?string
    {
        return $this->pluginName;
    }

    public function getPluginVersion(): ?string
    {
        return $this->pluginVersion;
    }

    public function getCustomFields(): array
    {
        return $this->customFields;
    }

    public function getPhpVersion(): string
    {
        return $this->phpVersion;
    }
}
