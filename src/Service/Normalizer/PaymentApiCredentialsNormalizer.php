<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer;

use Paysera\CheckoutSdk\Entity\PaymentApiCredentials;

class PaymentApiCredentialsNormalizer
{
    public function normalize(PaymentApiCredentials $apiCredentials): array
    {
        return [
            'client_id' => $apiCredentials->getClientId(),
            'client_secret' => $apiCredentials->getClientSecret(),
        ];
    }
}
