<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Provider;

use Paysera\CheckoutSdk\Entity\SupportContact;

class CommonInformationProvider
{
    public function getSupportContact(): SupportContact
    {
        return new SupportContact();
    }
}
