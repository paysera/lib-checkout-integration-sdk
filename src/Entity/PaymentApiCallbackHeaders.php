<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

class PaymentApiCallbackHeaders
{
    public const SIGNATURE_HEADER_NAME = 'X-Paysera-Signature';
    public const SIGNATURE_ALG_HEADER_NAME = 'X-Paysera-Signature-Alg';
    public const CREATED_AT_HEADER_NAME = 'X-Paysera-Created-At';
    public const REQUEST_ID_HEADER_NAME = 'X-Paysera-Request-Id';
    public const CALLBACK_ID_HEADER_NAME = 'X-Paysera-Callback-Id';

    private string $signature;
    private string $signatureAlg;
    private int $createdAt;
    private string $requestId;
    private string $callbackId;

    public function __construct(
        string $signature,
        string $signatureAlg,
        int $createdAt,
        string $requestId,
        string $callbackId
    ) {
        $this->signature = $signature;
        $this->signatureAlg = $signatureAlg;
        $this->createdAt = $createdAt;
        $this->requestId = $requestId;
        $this->callbackId = $callbackId;
    }

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function getSignatureAlg(): string
    {
        return $this->signatureAlg;
    }

    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function getCallbackId(): string
    {
        return $this->callbackId;
    }
}
