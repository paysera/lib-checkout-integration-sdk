<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

class PaymentMethodDisplayMode
{
    public const FULL_LOGO = 'full_logo';
    public const LOGO_AND_TITLE = 'logo_and_title';

    public const MODES = [
        self::FULL_LOGO,
        self::LOGO_AND_TITLE,
    ];
}
