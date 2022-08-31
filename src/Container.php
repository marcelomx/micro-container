<?php

declare(strict_types=1);

namespace MicroContainer;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private array $instances = [];

    public function __construct(private array $defs = [])
    {
    }

    public function get(string $id)
    {
        if (!$this->has($id)) {
            throw new NotFoundException(
                sprintf('No entry was found for "%s" identifier', $id)
            );
        }

        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        $def = $this->defs[$id];

        if (is_string($def) && class_exists($def)) {
            $def = function () use ($def) {
                $refClass = new \ReflectionClass($def);

                return $refClass->newInstanceArgs(
                    $this->resolveClassParams($refClass)
                );
            };
        }

        if (\is_callable($def)) {
            $newInstance = $def($this);

            return $this->registerNewInstance(
                $newInstance,
                $id,
                get_class($newInstance)
            );
        }

        return $def;
    }

    public function has(string $id): bool
    {
        return isset($this->defs[$id]);
    }

    private function resolveClassParams(\ReflectionClass $refClass): array
    {
        $constructor = $refClass->getConstructor();

        return !$constructor
            ? []
            : array_map(function (\ReflectionParameter $p) {
                $type = $p->getType();

                if ($type->isBuiltin()) {
                    return $p->isDefaultValueAvailable()
                        ? $p->getDefaultValue()
                        : $this->get($p->getName());
                }

                $classDef = $type->getName();
                $this->defs[$classDef] = $this->defs[$classDef] ?? $classDef;

                return $this->get($classDef);
            }, $constructor->getParameters());
    }

    private function registerNewInstance($newInstance, $id, $classDef): object
    {
        $this->instances[$id] = $newInstance;

        if (
            !isset($this->instances[$classDef])
            && $classDef !== $id
            && !$this->has($classDef)
        ) {
            $this->instances[$classDef] = $newInstance;
        }

        return $newInstance;
    }
}
