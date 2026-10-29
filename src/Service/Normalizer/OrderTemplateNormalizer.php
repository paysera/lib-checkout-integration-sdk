<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer;

use Paysera\CheckoutSdk\Entity\OrderTemplate;

class OrderTemplateNormalizer
{
    /**
     * Never fails: a missing or malformed template reads as null, a malformed name as an unknown one.
     *
     * @param mixed $data the order_template value of a callback order
     */
    public function denormalize($data): ?OrderTemplate
    {
        if (!is_array($data) || !is_string($data['id'] ?? null) || $data['id'] === '') {
            return null;
        }

        $name = $data['name'] ?? null;

        return new OrderTemplate($data['id'], is_string($name) && $name !== '' ? $name : null);
    }
}
