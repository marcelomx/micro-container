<?php

namespace MicroContainer\Tests;

use Psr\Container\ContainerInterface;

class Foo implements FooInterface
{
    public function __construct(
        public string $foo,
        public \stdClass $stdDep
    ) {
    }
}

class Bar
{
    public function __construct(public Foo $foo)
    {
    }
}


class Baz
{
    public function __construct(
        public FooInterface $foo,
        public Bar $bar,
        public ContainerAware $containerAware,
        public string $name = 'Default Value',
    ) {
    }
}

interface FooInterface
{
}

class ContainerAware
{
    public function __construct(public ContainerInterface $container)
    {
    }
}


class FooVariadic
{
    public array $foo = [];

    public function __construct(Foo ...$foo)
    {
        $this->foo = $foo;
    }
}
