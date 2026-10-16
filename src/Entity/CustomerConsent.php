<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

class CustomerConsent
{
    private string $text;
    private string $linkUrl;
    private string $linkText;

    public function __construct(string $text, string $linkUrl, string $linkText)
    {
        $this->text = $text;
        $this->linkUrl = $linkUrl;
        $this->linkText = $linkText;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getLinkUrl(): string
    {
        return $this->linkUrl;
    }

    public function getLinkText(): string
    {
        return $this->linkText;
    }
}
