<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Exception;

use Exception;
use Throwable;

abstract class BaseException extends Exception
{
    public const E_CONTAINER = 10;
    public const E_API_CLIENT = 20;
    public const E_SERIALIZATION = 30;
    public const E_NORMALIZATION = 40;
    public const E_REQUEST_FACTORY = 50;
    public const E_AUTH_TOKEN = 60;
    public const E_INVALID_TYPE = 70;
    public const E_VALIDATION = 80;
    public const E_RUNTIME = 90;
    public const E_VERIFICATION = 100;
    public const E_ARGUMENT = 110;
    public const E_JWT_VALIDATION = 120;
    public const E_CACHE = 130;

    /**
     * Reserved key under which getContextData() collects context that carries no key of its own.
     */
    private const UNSTRUCTURED_CONTEXT_KEY = '_raw';

    private string $context;

    /**
     * @var array<int|string, mixed>
     */
    private array $contextData = [];

    public function __construct(?string $message = null, ?int $code = null, ?Throwable $previous = null)
    {
        parent::__construct(
            $message ?? $this->getDefaultMessage(),
            $code ?? $this->getDefaultCode(),
            $previous
        );

        $this->context = '';
    }

    abstract protected function getDefaultCode(): int;

    protected function getDefaultMessage(): string
    {
        return '';
    }

    public function __toString(): string
    {
        if ($this->context === '') {
            return parent::__toString();
        }
        return 'Context: ' . $this->context . PHP_EOL .  parent::__toString();
    }

    /**
     * @param mixed $context
     */
    public function setContext($context): self
    {
        $this->addContextData($context);

        if (!is_string($context)) {
            $context = $this->dump($context);
        }

        $this->context .= $context;

        return $this;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * The diagnostic data behind getContext(), kept as an array so a PSR-3 logger normalizer
     * can render it without decoding a string first.
     *
     * It is not character-for-character the same data. A string-keyed bag is merged key by key
     * and a repeated key resolves last-wins, while getContext() concatenates both values; every
     * other shape is appended as a list under the reserved '_raw' key; and a value json_encode()
     * cannot render is kept as the original value rather than falling back to its print_r() form.
     * A value kept as given is handed to a log formatter as it is — an object stays an object.
     *
     * @return array<int|string, mixed>
     */
    public function getContextData(): array
    {
        return $this->contextData;
    }

    /**
     * One rule: a context that is an array keyed entirely by strings is a keyed bag and is merged
     * key by key. Anything else — a list, an integer-keyed array, a scalar, an object, a body that
     * is not JSON — carries no key of its own and is appended under UNSTRUCTURED_CONTEXT_KEY.
     *
     * Merging the second kind into the shared keyspace is what loses data: two list-shaped contexts
     * both write key 0, and an integer-keyed one is indistinguishable from an appended value.
     *
     * @param mixed $context
     */
    private function addContextData($context): void
    {
        if (is_string($context)) {
            if ($context === '') {
                return;
            }

            $decoded = json_decode($context, true);

            if (is_array($decoded)) {
                $context = $decoded;
            }
        }

        if (!is_array($context) || !$this->isKeyedBag($context)) {
            $this->appendUnstructured($context);

            return;
        }

        // A decoded API body may carry our reserved key. Route its value to the list the key is
        // documented to hold, so the key never degrades into whatever the API put there.
        if (array_key_exists(self::UNSTRUCTURED_CONTEXT_KEY, $context)) {
            $this->appendUnstructured($context[self::UNSTRUCTURED_CONTEXT_KEY]);

            unset($context[self::UNSTRUCTURED_CONTEXT_KEY]);
        }

        $this->contextData = array_replace($this->contextData, $context);
    }

    /**
     * @param array<int|string, mixed> $context
     */
    private function isKeyedBag(array $context): bool
    {
        foreach (array_keys($context) as $key) {
            if (!is_string($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param mixed $value
     */
    private function appendUnstructured($value): void
    {
        $unstructured = $this->contextData[self::UNSTRUCTURED_CONTEXT_KEY] ?? [];

        if (!is_array($unstructured)) {
            $unstructured = [$unstructured];
        }

        $unstructured[] = $value;

        $this->contextData[self::UNSTRUCTURED_CONTEXT_KEY] = $unstructured;
    }

    /**
     * @param mixed $data
     * @return string
     */
    private function dump($data): string
    {
        $result = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        if ($result === false) {
            return print_r($data, true);
        }

        return $result;
    }

}
