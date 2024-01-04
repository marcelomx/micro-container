<?php

declare(strict_types=1);

namespace MicroContainer\Exceptions;

use Psr\Container\ContainerExceptionInterface;

use ReflectionParameter;
use ReflectionProperty;

class ContainerException extends \Exception implements ContainerExceptionInterface
{
    public static function classNotExists(string $class): static
    {
        return new static(
            "Class '{$class}' not exists"
        );
    }

    public static function notInstantiable(string $class): static
    {
        return new static(
            "Target '{$class}' is not instantiable"
        );
    }

    public static function unableToResolveParameter(ReflectionParameter $parameter): static
    {
        return new static(sprintf(
            "Unable to resolve '%s' constructor parameter: '%s'",
            $parameter->getDeclaringClass()->name,
            $parameter->name
        ));
    }

    public static function unableToResolveAutowired(ReflectionProperty $property): static
    {
        return new static(sprintf(
            "Unable to resolve '%s' autowired property: '%s'",
            $property->getDeclaringClass()->name,
            $property->name
        ));
    }
}
