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

    private string $context;

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
