<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use Paysera\CheckoutSdk\Entity\Collection\ItemInterface;
use Paysera\CheckoutSdk\Entity\Collection\PaymentCountryCollection;

class PaymentMethod implements ItemInterface
{
    public const FLOW_REDIRECT = 'redirect';
    public const FLOW_DIRECT = 'direct';

    private string $key;
    private string $title;
    private string $description;
    private string $type;
    private string $flow;
    private PaymentCountryCollection $availableCountries;
    private string $logoUrl;
    private string $logoWideUrl;
    /**
     * @var array<string, PaymentMethodCountryDisplay>
     */
    private array $countryDisplays = [];
    private ?PaymentMethodDisplayOptions $displayOptions;

    /**
     * @param PaymentMethodCountryDisplay[] $countryDisplays
     */
    public function __construct(
        string $key,
        string $title,
        string $description,
        string $type,
        string $flow,
        PaymentCountryCollection $availableCountries,
        string $logoUrl = '',
        string $logoWideUrl = '',
        array $countryDisplays = [],
        ?PaymentMethodDisplayOptions $displayOptions = null
    ) {
        $this->key = $key;
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->flow = $flow;
        $this->availableCountries = $availableCountries;
        $this->logoUrl = $logoUrl;
        $this->logoWideUrl = $logoWideUrl;
        $this->displayOptions = $displayOptions;

        foreach ($countryDisplays as $countryDisplay) {
            $this->addCountryDisplay($countryDisplay);
        }
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getFlow(): string
    {
        return $this->flow;
    }

    public function getAvailableCountries(): PaymentCountryCollection
    {
        return $this->availableCountries;
    }

    public function getLogoUrl(): string
    {
        return $this->logoUrl;
    }

    public function getLogoWideUrl(): string
    {
        return $this->logoWideUrl;
    }

    /**
     * @return array<string, PaymentMethodCountryDisplay>
     */
    public function getCountryDisplays(): array
    {
        return $this->countryDisplays;
    }

    public function getCountryDisplay(?string $countryCode): ?PaymentMethodCountryDisplay
    {
        if ($countryCode === null) {
            return null;
        }

        return $this->countryDisplays[strtoupper(trim($countryCode))] ?? null;
    }

    public function getDisplayOptions(): ?PaymentMethodDisplayOptions
    {
        return $this->displayOptions;
    }

    public function withDisplayOptions(?PaymentMethodDisplayOptions $displayOptions): self
    {
        $paymentMethod = clone $this;
        $paymentMethod->displayOptions = $displayOptions;

        return $paymentMethod;
    }

    public function getTitleForCountry(?string $countryCode): string
    {
        $countryDisplay = $this->getCountryDisplay($countryCode);

        return $this->firstNonBlank(
            $countryDisplay !== null ? $countryDisplay->getTitle() : null,
            $this->title
        );
    }

    public function getDisplay(?string $countryCode = null): PaymentMethodDisplay
    {
        $title = $this->getTitleForCountry($countryCode);
        $countryDisplay = $this->isCountryLogosEnabled() ? $this->getCountryDisplay($countryCode) : null;

        if ($this->getDisplayMode() === PaymentMethodDisplayMode::FULL_LOGO) {
            $wideLogoUrl = $this->firstNonBlank(
                $countryDisplay !== null ? $countryDisplay->getLogoWideUrl() : null,
                $this->logoWideUrl
            );

            if ($wideLogoUrl !== '') {
                return new PaymentMethodDisplay($title, $wideLogoUrl, false);
            }
        }

        $logoUrl = $this->firstNonBlank(
            $countryDisplay !== null ? $countryDisplay->getLogoUrl() : null,
            $this->logoUrl
        );

        return new PaymentMethodDisplay($title, $logoUrl, true);
    }

    /**
     * @deprecated since the backend now returns `logo_url`; use getLogoUrl() instead.
     */
    public function getIconUrl(): string
    {
        return $this->getLogoUrl();
    }

    private function addCountryDisplay(PaymentMethodCountryDisplay $countryDisplay): void
    {
        if ($countryDisplay->getCountryCode() === '') {
            return;
        }

        $this->countryDisplays[$countryDisplay->getCountryCode()] = $countryDisplay;
    }

    private function getDisplayMode(): ?string
    {
        return $this->displayOptions !== null ? $this->displayOptions->getMode() : null;
    }

    private function isCountryLogosEnabled(): bool
    {
        return $this->displayOptions !== null && $this->displayOptions->isCountryLogosEnabled();
    }

    private function firstNonBlank(?string $override, string $default): string
    {
        return $override !== null && trim($override) !== '' ? $override : $default;
    }
}
