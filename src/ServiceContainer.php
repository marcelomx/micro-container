<?php

declare(strict_types=1);

namespace MicroContainer;

use Psr\Container\ContainerInterface;

class ServiceContainer implements ContainerInterface
{
    private array $instances = [];

    public function __construct(private array $definitions = [])
    {
        $this->instances[ContainerInterface::class] = $this;
    }

    /**
     * @template T
     * @param class-string<T> $id
     * @return T
     * @throws \MicroContainer\NotFoundException
     */
    public function get(string $id): object
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        $definition = $this->definitions[$id] ?? $id;

        if (\is_string($definition) && $definition !== $id) {
            return $this->get($definition);
        }

        try {
            $this->instances[$id] = $this->resolve($definition);
        } catch (\Exception $e) {
            throw NotFoundException::forEntry($id, $e);
        }

        return $this->instances[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->instances[$id])
            || isset($this->definitions[$id]);
    }

    private function resolve(string|callable $definition): object
    {
        if (\is_callable($definition)) {
            return $definition($this);
        }

        try {
            $reflection = new \ReflectionClass($definition);
        } catch (\ReflectionException) {
            throw ContainerException::classNotExists($definition);
        }

        if (!$reflection->isInstantiable()) {
            throw ContainerException::notInstantiable($definition);
        }

        $instance = $reflection->newInstanceArgs(array_map(
            $this->resolveParameter(...),
            $reflection->getConstructor()?->getParameters() ?? []
        ));

        foreach ($reflection->getProperties() as $property) {
            $this->resolveAutowired($property, $instance);
        }

        return $instance;
    }

    private function resolveParameter(\ReflectionParameter $parameter): mixed
    {
        if ($type = $parameter->getType()) {
            if (!$type instanceof \ReflectionNamedType) {
                throw ContainerException::unableToResolveParameter($parameter);
            }

            $typeName = $type->getName();

            if (!$type->isBuiltin()) {
                return $this->get($typeName);
            }

            if (
                ($typeName === 'array' && !$parameter->isDefaultValueAvailable()) ||
                $parameter->isVariadic()
            ) {
                return [];
            }
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw ContainerException::unableToResolveParameter($parameter);
    }

    private function resolveAutowired(\ReflectionProperty $property, object $instance)
    {
        $attributes = $property->getAttributes(Autowired::class);

        if (!$attributes) {
            return;
        }

        $typeName = $attributes[0]->newInstance()->service
            ?? $property->getType()?->getName();

        if (!$typeName) {
            throw ContainerException::unableToResolveAutowired($property);
        }

        $property->setValue($instance, $this->get($typeName));
    }
}
