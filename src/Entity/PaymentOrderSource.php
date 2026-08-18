<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

class PaymentOrderSource implements EnumInterface
{
    public const CHECKOUT_PAGE = 'checkout_page';
    public const PRODUCT_PAGE_EXPRESS_CHECKOUT = 'product_page_express_checkout';

    public const SOURCES = [
        self::CHECKOUT_PAGE,
        self::PRODUCT_PAGE_EXPRESS_CHECKOUT,
    ];

    private string $source;

    public function __construct(string $source)
    {
        $this->source = $source;
    }

    public function getValue(): string
    {
        return $this->source;
    }
}
