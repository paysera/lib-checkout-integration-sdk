<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Callback;

use Paysera\CheckoutSdk\Entity\CallbackEvent;
use Paysera\CheckoutSdk\Exception\ValidationException;
use Paysera\CheckoutSdk\Service\Validator\CallbackEventValidator;
use Paysera\CheckoutSdk\Util\TypeConverter;

class CallbackEventBuilder
{
    private TypeConverter $typeConverter;
    private CallbackEventValidator $callbackEventValidator;

    public function __construct(
        TypeConverter $typeConverter,
        CallbackEventValidator $callbackEventValidator
    ) {
        $this->typeConverter = $typeConverter;
        $this->callbackEventValidator = $callbackEventValidator;
    }

    /**
     * @param array<string, mixed> $payload
     * @throws ValidationException
     */
    public function build(array $payload): CallbackEvent
    {
        $eventData = $payload['event'] ?? null;

        if (!is_array($eventData)) {
            throw (new ValidationException())
                ->setContext(['event' => 'event is required.'])
            ;
        }

        $event = new CallbackEvent(
            $this->extractField($eventData, 'name'),
            $this->extractField($eventData, 'type')
        );

        $this->callbackEventValidator->validate($event);

        return $event;
    }

    /**
     * @param array<mixed> $eventData
     */
    private function extractField(array $eventData, string $key): string
    {
        $value = $eventData[$key] ?? null;

        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            return '';
        }

        return $this->typeConverter->convert($value, TypeConverter::STRING);
    }
}
