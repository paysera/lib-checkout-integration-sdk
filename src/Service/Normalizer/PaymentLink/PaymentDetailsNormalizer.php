<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer\PaymentLink;

use Paysera\CheckoutSdk\Entity\PaymentLink\PaymentDetails;

class PaymentDetailsNormalizer
{
    public function normalize(PaymentDetails $paymentDetails): array
    {
        $result = [];

        if ($paymentDetails->getKey() !== null) {
            $result['key'] = $paymentDetails->getKey();
        }

        if ($paymentDetails->getPurpose() !== null) {
            $result['purpose'] = $paymentDetails->getPurpose();
        }

        if ($paymentDetails->getCountryCode() !== null) {
            $result['country_code'] = $paymentDetails->getCountryCode();
        }

        return $result;
    }

    public function denormalize(array $data): PaymentDetails
    {
        return new PaymentDetails(
            isset($data['key']) ? (string) $data['key'] : null,
            isset($data['purpose']) ? (string) $data['purpose'] : null,
            isset($data['country_code']) ? (string) $data['country_code'] : null
        );
    }
}
