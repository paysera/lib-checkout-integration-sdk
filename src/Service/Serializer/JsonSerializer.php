<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Serializer;

use JsonException;
use Paysera\CheckoutSdk\Exception\SerializerException;

class JsonSerializer
{
    /**
     * @throws SerializerException
     */
    public function serialize(array $data): string
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new SerializerException(null, null, $exception);
        }
    }

    /**
     * @throws SerializerException
     */
    public function deserialize(string $jsonData): array
    {
        try {
            return json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new SerializerException(null, null, $exception);
        }
    }
}
