<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Facade;

use Paysera\CheckoutSdk\Entity\CallbackInterface;
use Paysera\CheckoutSdk\Entity\CallbackEvent;
use Paysera\CheckoutSdk\Exception\BaseException;
use Paysera\CheckoutSdk\Exception\CallbackBuildIntegrationException;
use Paysera\CheckoutSdk\Exception\CallbackVerificationIntegrationException;
use Paysera\CheckoutSdk\Exception\UnsupportedCallbackIntegrationException;
use Paysera\CheckoutSdk\Service\Callback\CallbackEventBuilder;
use Paysera\CheckoutSdk\Service\Callback\CallbackHandlerRegistry;
use Paysera\CheckoutSdk\Service\Callback\Handler\CallbackHandlerInterface;
use Paysera\CheckoutSdk\Service\PaymentApiCallbackVerifier;
use Paysera\CheckoutSdk\Service\Serializer\JsonSerializer;
use Paysera\CheckoutSdk\Util\MessagePayloadExtractor;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

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
     * @throws UnsupportedCallbackIntegrationException
     */
    public function processCallback(RequestInterface $request): CallbackInterface
    {
        $this->verifyCallback($request);
        $payload = $this->decodePayload($request);
        $event = $this->callbackEventBuilder->build($payload);
        $handler = $this->resolveHandler($event);

        return $this->handleCallback($handler, $payload, $event);
    }

    /**
     * @throws CallbackVerificationIntegrationException
     */
    private function verifyCallback(RequestInterface $request): void
    {
        try {
            $this->paymentApiCallbackVerifier->verify($request);
        } catch (BaseException $exception) {
            $this->logger->error(
                'Unable to verify payment callback request',
                [
                    'exception' => $exception,
                ]
            );

            throw new CallbackVerificationIntegrationException(
                'Unable to verify payment callback request',
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @return array<string, mixed>
     * @throws CallbackBuildIntegrationException
     */
    private function decodePayload(RequestInterface $request): array
    {
        try {
            $payload = $this->messagePayloadExtractor->getPayload($request);

            return $this->jsonSerializer->deserialize($payload);
        } catch (BaseException $exception) {
            $this->logger->error(
                self::MESSAGE_BUILD_FAILED,
                [
                    'exception' => $exception->getMessage(),
                ]
            );

            throw new CallbackBuildIntegrationException(
                self::MESSAGE_BUILD_FAILED,
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws UnsupportedCallbackIntegrationException
     */
    private function resolveHandler(CallbackEvent $event): CallbackHandlerInterface
    {
        $handler = $this->callbackHandlerRegistry->resolve($event);

        if ($handler !== null) {
            return $handler;
        }

        $this->logger->warning(
            'Unsupported callback received',
            [
                'type' => $event->getType(),
                'name' => $event->getName(),
            ]
        );

        throw new UnsupportedCallbackIntegrationException('Unsupported callback received');
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
        try {
            return $handler->handle($payload, $event);
        } catch (BaseException $exception) {
            $this->logger->error(
                self::MESSAGE_BUILD_FAILED,
                [
                    'exception' => $exception,
                ]
            );

            throw new CallbackBuildIntegrationException(
                self::MESSAGE_BUILD_FAILED,
                $exception->getCode(),
                $exception
            );
        }
    }
}
