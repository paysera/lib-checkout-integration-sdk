<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer\PaymentLink;

use Paysera\CheckoutSdk\Entity\PaymentLink\PayerInformation;

class PayerInformationNormalizer
{
    public function normalize(PayerInformation $payerInformation): array
    {
        $result = [];

        if ($payerInformation->getName() !== null) {
            $result['name'] = $payerInformation->getName();
        }

        if ($payerInformation->getEmail() !== null) {
            $result['email'] = $payerInformation->getEmail();
        }

        return $result;
    }

    public function denormalize(array $data): PayerInformation
    {
        return new PayerInformation(
            isset($data['name']) ? (string) $data['name'] : null,
            isset($data['email']) ? (string) $data['email'] : null
        );
    }
}
