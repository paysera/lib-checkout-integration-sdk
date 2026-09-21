<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

class SupportContact
{
    private string $email = 'support@paysera.com';
    private string $phone = '+44 20 80996963';
    private string $website = 'https://www.paysera.com/v2/en-GB/contacts';

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getWebsite(): string
    {
        return $this->website;
    }
}
