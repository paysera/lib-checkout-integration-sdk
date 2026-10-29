<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

/**
 * Returned by Callbacks::processCallback() when a callback is authentic and well-formed but carries
 * an event this SDK version has no handler for.
 *
 * An unsupported event is not a failure: the payload is valid and Paysera must not retry it. The SDK
 * therefore degrades to this value object instead of throwing, so consumers acknowledge the delivery
 * through a normal instanceof branch rather than a catch block.
 *
 * getPayload() exposes the decoded body so an integration can still act on an event the SDK does not
 * model yet — log it, queue it, or handle it itself. The payload has passed signature verification but
 * has NOT been normalized or validated by the SDK; treat it as raw input.
 *
 * See docs/PROCESS_PAYMENT_CALLBACK.md.
 */
class UnsupportedCallback implements CallbackInterface
{
    private CallbackEventInterface $event;

    /**
     * @var array<string, mixed>
     */
    private array $payload;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(CallbackEventInterface $event, array $payload)
    {
        $this->event = $event;
        $this->payload = $payload;
    }

    public function getEvent(): CallbackEventInterface
    {
        return $this->event;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
}
