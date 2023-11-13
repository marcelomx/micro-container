<?php

declare(strict_types=1);

namespace MicroContainer;

use Psr\Container\ContainerExceptionInterface;

class ContainerException extends \Exception implements ContainerExceptionInterface
{
    public static function classNotExists(string $class): static
    {
        return new static("Class '{$class}' not exists");
    }

    public static function notInstantiable(string $class): static
    {
        return new static("Target '{$class}' is not instantiable");
    }

    public static function unableToResolveParameter(\ReflectionParameter $parameter): static
    {
        return new static("Unable to resolve constructor parameter '{$parameter->name}'");
    }
}
