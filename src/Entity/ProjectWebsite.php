<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use Paysera\CheckoutSdk\Entity\Collection\ItemInterface;

class ProjectWebsite implements ItemInterface
{
    private string $url;
    private bool $verified;

    public function __construct(string $url, bool $verified)
    {
        $this->url = $url;
        $this->verified = $verified;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }
}
