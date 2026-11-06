<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Facade;

use Paysera\CheckoutSdk\Entity\CallbackEvent;
use Paysera\CheckoutSdk\Entity\CallbackInterface;
use Paysera\CheckoutSdk\Entity\UnsupportedCallback;
use Paysera\CheckoutSdk\Exception\BaseException;
use Paysera\CheckoutSdk\Exception\CallbackBuildIntegrationException;
use Paysera\CheckoutSdk\Exception\CallbackVerificationIntegrationException;
use Paysera\CheckoutSdk\Service\Callback\CallbackEventBuilder;
use Paysera\CheckoutSdk\Service\Callback\CallbackHandlerRegistry;
use Paysera\CheckoutSdk\Service\Callback\Handler\CallbackHandlerInterface;
use Paysera\CheckoutSdk\Service\PaymentApiCallbackVerifier;
use Paysera\CheckoutSdk\Service\Serializer\JsonSerializer;
use Paysera\CheckoutSdk\Util\MessagePayloadExtractor;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

/**
 * An event this SDK version has no handler for is not treated as a failure: processCallback()
 * returns an UnsupportedCallback instead of throwing, so consumers acknowledge the delivery with
 * HTTP 200 through a normal instanceof branch and Paysera stops retrying it. Only a callback that
 * cannot be trusted (signature) or cannot be read (payload shape) surfaces as an IntegrationException.
 */
class Callbacks
{
    private const MESSAGE_BUILD_FAILED = 'Unable to build payment callback request';

    private LoggerInterface $logger;
    private PaymentApiCallbackVerifier $paymentApiCallbackVerifier;
    private MessagePayloadExtractor $messagePayloadExtractor;
    private JsonSerializer $jsonSerializer;
    private CallbackEventBuilder $callbackEventBuilder;
    private CallbackHandlerRegistry $callbackHandlerRegistry;

    public function __construct(
        LoggerInterface $logger,
        PaymentApiCallbackVerifier $paymentApiCallbackVerifier,
        MessagePayloadExtractor $messagePayloadExtractor,
        JsonSerializer $jsonSerializer,
        CallbackEventBuilder $callbackEventBuilder,
        CallbackHandlerRegistry $callbackHandlerRegistry
    ) {
        $this->logger = $logger;
        $this->paymentApiCallbackVerifier = $paymentApiCallbackVerifier;
        $this->messagePayloadExtractor = $messagePayloadExtractor;
        $this->jsonSerializer = $jsonSerializer;
        $this->callbackEventBuilder = $callbackEventBuilder;
        $this->callbackHandlerRegistry = $callbackHandlerRegistry;
    }

    /**
     * @throws CallbackVerificationIntegrationException
     * @throws CallbackBuildIntegrationException
     */
    public function processCallback(RequestInterface $request): CallbackInterface
    {
        $this->verifyCallback($request);
        $payload = $this->decodePayload($request);
        $event = $this->buildEvent($payload);
        $handler = $this->callbackHandlerRegistry->resolve($event);

        if ($handler === null) {
            return $this->createUnsupportedCallback($event, $payload);
        }

        return $this->handleCallback($handler, $payload, $event);
    }

    /**
     * @throws CallbackVerificationIntegrationException
     */
    private function verifyCallback(RequestInterface $request): void
    {
        $message = 'Unable to verify payment callback request';

        try {
            $this->paymentApiCallbackVerifier->verify($request);
        } catch (BaseException $exception) {
            $this->logFailure($message, $exception);

            throw new CallbackVerificationIntegrationException($message, $exception->getCode(), $exception);
        }
    }

    /**
     * @return array<string, mixed>
     * @throws CallbackBuildIntegrationException
     */
    private function decodePayload(RequestInterface $request): array
    {
        return $this->guardBuild(function () use ($request): array {
            $payload = $this->messagePayloadExtractor->getPayload($request);

            return $this->jsonSerializer->deserialize($payload);
        });
    }

    /**
     * @param array<string, mixed> $payload
     * @throws CallbackBuildIntegrationException
     */
    private function buildEvent(array $payload): CallbackEvent
    {
        return $this->guardBuild(function () use ($payload): CallbackEvent {
            return $this->callbackEventBuilder->build($payload);
        });
    }

    /**
     * @param array<string, mixed> $payload
     * @throws CallbackBuildIntegrationException
     */
    private function handleCallback(
        CallbackHandlerInterface $handler,
        array $payload,
        CallbackEvent $event
    ): CallbackInterface {
        return $this->guardBuild(function () use ($handler, $payload, $event): CallbackInterface {
            return $handler->handle($payload, $event);
        });
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function createUnsupportedCallback(CallbackEvent $event, array $payload): UnsupportedCallback
    {
        $this->logger->info(
            'Unsupported callback received',
            [
                'type' => $event->getType(),
                'name' => $event->getName(),
            ]
        );

        return new UnsupportedCallback($event, $payload);
    }

    /**
     * Every build step fails the same way: log the internal exception with its validation context,
     * then surface it to the consumer as the documented CallbackBuildIntegrationException.
     *
     * @template T
     * @param callable(): T $operation
     * @return T
     * @throws CallbackBuildIntegrationException
     */
    private function guardBuild(callable $operation)
    {
        try {
            return $operation();
        } catch (BaseException $exception) {
            $this->logFailure(self::MESSAGE_BUILD_FAILED, $exception);

            throw new CallbackBuildIntegrationException(
                self::MESSAGE_BUILD_FAILED,
                $exception->getCode(),
                $exception
            );
        }
    }

    private function logFailure(string $message, BaseException $exception): void
    {
        $this->logger->error(
            $message,
            [
                'exception' => $exception,
                'exception_context' => $exception->getContextData(),
            ]
        );
    }
}
