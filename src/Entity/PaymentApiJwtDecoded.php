<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

use Paysera\CheckoutSdk\Util\SensitiveValue;

/**
 * Wrapper for decoded JWT payload providing type-safe access to claims.
 */
class PaymentApiJwtDecoded
{
    private SensitiveValue $jwtDecodedPayload;

    public function __construct(SensitiveValue $jwtDecodedPayload)
    {
        $this->jwtDecodedPayload = $jwtDecodedPayload;
    }

    public function getExpiresAt(): int
    {
        return $this->jwtDecodedPayload->get()->exp;
    }

    public function getIssuedAt(): int
    {
        return $this->jwtDecodedPayload->get()->iat;
    }

    public function getType(): string
    {
        return $this->jwtDecodedPayload->get()->typ;
    }

    public function getProjectId(): string
    {
        return $this->jwtDecodedPayload->get()->project_id;
    }

    public function getClientId(): string
    {
        return $this->jwtDecodedPayload->get()->client_id;
    }
}
