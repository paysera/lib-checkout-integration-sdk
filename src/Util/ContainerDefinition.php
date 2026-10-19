<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Util;

class ContainerDefinition
{
    private array $envMappings;
    private array $arguments;
    private ?string $alias;

    public function __construct()
    {
        $this->envMappings = [];
        $this->arguments = [];
        $this->alias = null;
    }

    /**
     * Map constructor parameter to environment variable
     *
     * @param mixed $defaultValue Default value if env variable is not set
     */
    public function setEnv(string $envVarName, string $paramName, $defaultValue = null): self
    {
        $this->envMappings[$paramName] = [
            'envVarName' => $envVarName,
            'default' => $defaultValue,
        ];

        return $this;
    }

    /**
     * Set direct value for constructor parameter
     *
     * @param mixed $value Value to inject
     */
    public function setArgument(string $paramName, $value): self
    {
        $this->arguments[$paramName] = $value;

        return $this;
    }

    /**
     * Check if parameter has definition (direct argument or env mapping)
     */
    public function hasArgument(string $paramName): bool
    {
        return isset($this->arguments[$paramName]) || isset($this->envMappings[$paramName]);
    }

    /**
     * Resolve parameter value
     * Priority: direct argument > environment variable > null
     *
     * @return mixed
     */
    public function resolveArgument(string $paramName)
    {
        // Priority 1: Direct argument
        if (isset($this->arguments[$paramName])) {
            return $this->arguments[$paramName];
        }

        // Priority 2: Environment variable
        if (isset($this->envMappings[$paramName])) {
            $mapping = $this->envMappings[$paramName];
            return EnvReader::getAsString($mapping['envVarName'], $mapping['default']);
        }

        // No definition found
        return null;
    }

    public function setAlias(string $className): self
    {
        $this->alias = $className;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function hasAlias(): bool
    {
        return $this->alias !== null;
    }
}
