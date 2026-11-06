<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\JWT;

use Firebase\JWT\JWT;
use stdClass;

class JwtBridge
{
    /**
     * Default clock-skew allowance (seconds) for JWT time-claim validation
     * (iat, nbf, exp). Defaults to 0 — the SDK does not relax validation on its
     * own; the consuming integration decides the tolerance via
     * SdkFacadeBuilder::setJwtLeeway(). A non-zero leeway absorbs clock drift
     * between the integrating server and Paysera (RFC 7519 allows small leeway
     * for clock skew), which otherwise causes BeforeValidException
     * ("Cannot handle token with iat prior to ...").
     */
    public const DEFAULT_LEEWAY_SECONDS = 0;

    private int $leewaySeconds;

    public function __construct(?int $leewaySeconds = null)
    {
        $this->leewaySeconds = $leewaySeconds ?? self::DEFAULT_LEEWAY_SECONDS;
    }

    public function getLeewaySeconds(): int
    {
        return $this->leewaySeconds;
    }

    /**
     * Decode and verify a JWT.
     *
     * firebase/php-jwt exposes leeway only as the global static
     * `Firebase\JWT\JWT::$leeway` (no per-call option), so it must be set before
     * decoding. To avoid leaking the SDK's value into a host application that
     * also uses firebase/php-jwt directly, the previous value is captured and
     * restored afterwards, keeping the mutation scoped to this call.
     */
    public function decode(
        string $jwt,
        $keyOrKeyArray,
        ?stdClass &$headers = null
    ): stdClass {
        $previousLeeway = JWT::$leeway;
        JWT::$leeway = $this->leewaySeconds;

        try {
            return JWT::decode($jwt, $keyOrKeyArray, $headers);
        } finally {
            JWT::$leeway = $previousLeeway;
        }
    }
}
