<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer\PaymentLink;

use Paysera\CheckoutSdk\Entity\PaymentLink\Experience;

class ExperienceNormalizer
{
    public function normalize(Experience $experience): array
    {
        $result = [
            'language' => $experience->getLanguage(),
        ];

        if ($experience->getPaymentFlow() !== null) {
            $result['payment_flow'] = $experience->getPaymentFlow();
        }

        return $result;
    }

    public function denormalize(array $data): Experience
    {
        return new Experience(
            (string) ($data['language'] ?? ''),
            isset($data['payment_flow']) ? (string) $data['payment_flow'] : null
        );
    }
}
