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

        if (null === ($construtor = $reflection->getConstructor())) {
            return $reflection->newInstance();
        }

        return $reflection->newInstanceArgs(array_map(
            $this->resolveParameter(...),
            $construtor->getParameters()
        ));
    }

    private function resolveParameter(\ReflectionParameter $parameter): mixed
    {
        /** @var \ReflectionNamedType */
        if ($type = $parameter->getType()) {
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
}
