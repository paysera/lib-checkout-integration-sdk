<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Util;

use Exception;
use Paysera\CheckoutSdk\Exception\ContainerException;
use Paysera\CheckoutSdk\Exception\ContainerNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

class Container implements ContainerInterface
{
    protected array $instances;
    private array $definitions;

    public function __construct()
    {
        $this->definitions = [];
        $this->instances = [
            ContainerInterface::class => $this,
        ];
    }

    public function has(string $id): bool
    {
        return isset($this->instances[$id]);
    }

    public function get(string $id): object
    {
        $definition = $this->getDefinition($id);

        if ($definition !== null && $definition->hasAlias()) {
            return $this->get($definition->getAlias());
        }

        if ($this->has($id) === false) {
            try {
                $instance = $this->build($id);
            } catch (Exception $exception) {
                throw new ContainerNotFoundException(
                    'Service with id `'.$id.'` not found in container.',
                    null,
                    $exception
                );
            }

            $this->set($id, $instance);
        }

        return $this->instances[$id];
    }

    public function set(string $id, ?object $concrete = null): void
    {
        $this->instances[$id] = $concrete;
    }

    public function setDefinition(string $id): ContainerDefinition
    {
        $definition = new ContainerDefinition();
        $this->definitions[$id] = $definition;

        return $definition;
    }

    public function getDefinition(string $id): ?ContainerDefinition
    {
        return $this->definitions[$id] ?? null;
    }

    /**
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function build(string $id): object
    {
        $definition = $this->getDefinition($id);

        try {
            $instance = $this->createInstance($id, $definition);
        } catch (ReflectionException $exception) {
            throw new ContainerException('Class '.$id.' has instantiable issues.', null, $exception);
        }

        return $instance;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    protected function createInstance(string $className, ?ContainerDefinition $definition = null): object
    {
        $reflector = new ReflectionClass($className);

        if (!$reflector->isInstantiable()) {
            throw new ContainerException("Class $className is not instantiable");
        }

        $constructor = $reflector->getConstructor();
        if ($constructor === null) {
            return $reflector->newInstance();
        }

        $constructorParameters = $constructor->getParameters();
        $dependencies = $this->getDependencies($constructorParameters, $definition);

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * @param ReflectionParameter[] $constructorParameters
     * @return mixed[]
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getDependencies(array $constructorParameters, ?ContainerDefinition $definition = null): array
    {
        $dependencies = [];

        foreach ($constructorParameters as $constructorParameter) {
            $dependencies[] = $this->resolveParameter($constructorParameter, $definition);
        }

        return $dependencies;
    }

    /**
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function resolveParameter(ReflectionParameter $parameter, ?ContainerDefinition $definition)
    {
        $paramName = $parameter->getName();

        if ($definition !== null && $definition->hasArgument($paramName)) {
            return $definition->resolveArgument($paramName);
        }

        /** @var ReflectionNamedType|null $type */
        $type = $parameter->getType();

        if ($type === null) {
            return $this->resolveUntypedParameter($parameter);
        }

        if ($type->isBuiltin()) {
            return $this->resolveBuiltinParameter($parameter);
        }

        return $this->resolveClassParameter($parameter, $type);
    }

    /**
     * @throws ContainerException
     */
    private function resolveUntypedParameter(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new ContainerException('Can not resolve class dependency ' . $parameter->getName());
    }

    /**
     * @throws ContainerException
     */
    private function resolveBuiltinParameter(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new ContainerException(
            'Can not resolve class dependency ' . $parameter->getName() . ' without a default value or definition'
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function resolveClassParameter(ReflectionParameter $parameter, ReflectionNamedType $type)
    {
        if ($type->allowsNull() && $parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        return $this->get($type->getName());
    }
}
