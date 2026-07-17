<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Callback;

use Paysera\CheckoutSdk\Entity\CallbackEvent;
use Paysera\CheckoutSdk\Util\TypeConverter;

class CallbackEventBuilder
{
    private TypeConverter $typeConverter;

    public function __construct(TypeConverter $typeConverter)
    {
        $this->typeConverter = $typeConverter;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function build(array $payload): CallbackEvent
    {
        $eventData = $payload['event'] ?? [];

        if (!is_array($eventData)) {
            $eventData = [];
        }

        return new CallbackEvent(
            $this->typeConverter->convert($eventData['name'] ?? '', TypeConverter::STRING),
            $this->typeConverter->convert($eventData['type'] ?? '', TypeConverter::STRING)
        );
    }
}
