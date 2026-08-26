<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\JWT;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Paysera\CheckoutSdk\Exception\JwtValidationException;
use DateTimeImmutable;
use Paysera\CheckoutSdk\Service\Provider\PaymentApiUrlProvider;
use Paysera\CheckoutSdk\Util\SensitiveValue;
use Firebase\JWT\CachedKeySet;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Clock\ClockInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Decodes and validates JWT access tokens from Payment API using JWKS.
 *
 * Validates token signature against Keycloak JWKS endpoint and ensures
 * all required claims are present with correct types.
 */
class JwtDecoder implements PaymentApiJwtDecoderInterface, PaymentApiJwtValidatorInterface
{
    /**
     * Cache JWKS keys for 30 days.
     *
     * Strategy: cold-start fetch + long-lived cache. The /certs endpoint is
     * hit only on the first validation and whenever an incoming JWT carries
     * a `kid` absent from the cached key set (Keycloak rotation escape hatch,
     * handled natively by Firebase\JWT\CachedKeySet on unknown kid).
     *
     * Precondition: the injected CacheItemPoolInterface must be persistent
     * across PHP request lifecycles (Redis / APCu / filesystem). The default
     * InMemoryCache does not survive between requests and negates this
     * strategy - plugins must override it via
     * SdkFacadeBuilder::setCacheItemPool().
     */
    private const JWKS_CACHE_TTL = 30 * 24 * 60 * 60;

    /**
     * Enable Firebase\JWT\CachedKeySet built-in 10-calls-per-minute throttle
     * on the shared PSR-6 pool. Protects /certs from unknown-kid amplification
     * when forged JWTs arrive with random kid values.
     */
    private const JWKS_RATE_LIMIT_ENABLED = true;

    private JwtBridge $bridge;
    private PaymentApiUrlProvider $paymentApiUrlProvider;
    private ClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;
    private CacheItemPoolInterface $cache;
    private LoggerInterface $logger;
    private ClockInterface $clock;
    private ?CachedKeySet $keySet = null;

    public function __construct(
        JwtBridge $bridge,
        PaymentApiUrlProvider $paymentApiUrlProvider,
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        CacheItemPoolInterface $cache,
        LoggerInterface $logger,
        ClockInterface $clock
    ) {
        $this->bridge = $bridge;
        $this->paymentApiUrlProvider = $paymentApiUrlProvider;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->clock = $clock;
    }

    /**
     * @throws JwtValidationException
     * @throws ExpiredException
     */
    public function decode(string $jwt): SensitiveValue
    {
        try {
            if ($this->keySet === null) {
                $this->keySet = new CachedKeySet(
                    $this->paymentApiUrlProvider->getJwksUrl(),
                    $this->httpClient,
                    $this->requestFactory,
                    $this->cache,
                    self::JWKS_CACHE_TTL,
                    self::JWKS_RATE_LIMIT_ENABLED
                );
            }

            $decoded = $this->bridge->decode($jwt, $this->keySet);

            $this->validateRequiredClaims($decoded);

            return new SensitiveValue($decoded);
        } catch (BeforeValidException $exception) {
            $this->logger
                ->error(
                    'JWT validation failed: token not yet valid (clock skew between this '
                    . 'server and Paysera). Synchronize the server clock (NTP).',
                    $this->buildJwtDiagnosticContext($jwt, $exception, 'clock_skew_or_nbf')
                )
            ;

            throw new JwtValidationException(
                sprintf('JWT token validation failed: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        } catch (ExpiredException $exception) {
            $this->logger
                ->warning(
                    'JWT token is expired',
                    $this->buildJwtDiagnosticContext($jwt, $exception, 'expired')
                )
            ;

            throw $exception;
        } catch (SignatureInvalidException $exception) {
            $this->logger
                ->error(
                    'JWT validation failed: signature invalid (JWKS key mismatch or tampered token).',
                    $this->buildJwtDiagnosticContext($jwt, $exception, 'invalid_signature')
                )
            ;

            throw new JwtValidationException(
                sprintf('JWT token validation failed: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        } catch (JwtValidationException $exception) {
            $this->logger
                ->error(
                    'JWT validation failed: required claim missing or invalid.',
                    $this->buildJwtDiagnosticContext($jwt, $exception, 'invalid_claims')
                )
            ;

            throw $exception;
        } catch (Throwable $exception) {
            $this->logger
                ->error(
                    'JWT validation failed',
                    $this->buildJwtDiagnosticContext($jwt, $exception, 'unknown')
                )
            ;

            throw new JwtValidationException(
                sprintf('JWT token validation failed: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * Build a structured, non-sensitive diagnostic context for JWT failures.
     *
     * Decodes the payload WITHOUT verification (for logging only) to expose the
     * time-based claims and the exact skew against the local clock, so clock-skew
     * failures are immediately identifiable in logs. The raw token and signature
     * are never logged.
     *
     * @return array<string, mixed>
     */
    private function buildJwtDiagnosticContext(string $jwt, Throwable $exception, string $reason): array
    {
        $now = $this->clock->now()->getTimestamp();
        $context = [
            'reason' => $reason,
            'exception_class' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_code' => $exception->getCode(),
            'local_time' => $now,
            'local_time_utc' => $this->formatUtc($now),
            'configured_leeway_seconds' => $this->bridge->getLeewaySeconds(),
        ];

        $claims = $this->extractUnverifiedTimeClaims($jwt);
        if (isset($claims['iat']) && is_int($claims['iat'])) {
            $context['token_iat'] = $claims['iat'];
            $context['token_iat_utc'] = $this->formatUtc($claims['iat']);
            $context['iat_minus_local_seconds'] = $claims['iat'] - $now;
        }
        if (isset($claims['exp']) && is_int($claims['exp'])) {
            $context['token_exp'] = $claims['exp'];
            $context['token_exp_utc'] = $this->formatUtc($claims['exp']);
            $context['exp_minus_local_seconds'] = $claims['exp'] - $now;
        }
        if (isset($claims['nbf']) && is_int($claims['nbf'])) {
            $context['token_nbf'] = $claims['nbf'];
            $context['nbf_minus_local_seconds'] = $claims['nbf'] - $now;
        }

        return $context;
    }

    /**
     * Format a Unix timestamp as a UTC ISO-8601 string for diagnostic logging.
     */
    private function formatUtc(int $timestamp): string
    {
        return (new DateTimeImmutable('@' . $timestamp))->format('Y-m-d\TH:i:s\Z');
    }

    /**
     * Decode iat/exp/nbf from the JWT payload WITHOUT signature verification.
     * For diagnostics only — never trust these values for authorization.
     *
     * @return array<string, mixed>
     */
    private function extractUnverifiedTimeClaims(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return [];
        }

        $decoded = base64_decode(strtr($parts[1], '-_', '+/'), true);
        if ($decoded === false) {
            return [];
        }

        $payload = json_decode($decoded, true);
        if (!is_array($payload)) {
            return [];
        }

        return array_intersect_key($payload, ['iat' => true, 'exp' => true, 'nbf' => true]);
    }

    /**
     * @throws JwtValidationException
     * @throws ExpiredException
     *
     * @uses decode
     */
    public function validate(string $jwt): void
    {
        $this->decode($jwt);
    }

    /**
     * Validate that required claims are present with correct types.
     *
     * Required claims per Payment API JWT specification:
     * - exp (int): Token expiration timestamp (Unix timestamp)
     * - iat (int): Token issued at timestamp (Unix timestamp)
     * - typ (string): Token type (e.g., "Bearer")
     * - project_id (string): Paysera project UUID
     * - client_id (string): OAuth client identifier
     *
     * @throws JwtValidationException If any claim is missing or has wrong type
     */
    private function validateRequiredClaims(object $claims): void
    {
        if (!isset($claims->exp) || !is_int($claims->exp)) {
            throw new JwtValidationException('Missing or invalid exp claim');
        }
        if (!isset($claims->iat) || !is_int($claims->iat)) {
            throw new JwtValidationException('Missing or invalid iat claim');
        }
        if (!isset($claims->typ) || $claims->typ !== 'Bearer') {
            throw new JwtValidationException('Missing or invalid typ claim');
        }
        if (!isset($claims->project_id) || !is_string($claims->project_id) || trim($claims->project_id) === '') {
            throw new JwtValidationException('Missing or invalid project_id claim');
        }
        if (!isset($claims->client_id) || !is_string($claims->client_id) || trim($claims->client_id) === '') {
            throw new JwtValidationException('Missing or invalid client_id claim');
        }
    }
}
