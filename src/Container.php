<?php

declare(strict_types=1);

namespace MicroContainer;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
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
            $reflectionClass = new \ReflectionClass($definition);
        } catch (\ReflectionException) {
            throw ContainerException::classNotExists($definition);
        }

        if (!$reflectionClass->isInstantiable()) {
            throw ContainerException::notInstantiable($definition);
        }

        return new $definition(...$this->resolveDependencies(
            $reflectionClass->getConstructor()?->getParameters() ?? []
        ));
    }

    /** @param \ReflectionParameter[] $parameters */
    private function resolveDependencies(array $parameters): array
    {
        $results = [];

        foreach ($parameters as $parameter) {
            /** @var \ReflectionNamedType */
            $type = $parameter->getType();

            $results[] = match (true) {
                $parameter->isDefaultValueAvailable() => $parameter->getDefaultValue(),
                $parameter->isVariadic() => [],
                default => $this->get($type->getName())
            };
        }

        return $results;
    }
}
