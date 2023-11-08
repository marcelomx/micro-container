# Micro Container

A micro PHP Dependency Injection Container.

## Installation

```bash
composer install marcelomx/micro-container
```

## Usage

```php

use MicroContainer\ServiceContainer;

interface FooInterface
{
}

class Foo implements FooInterface
{
    public function __construt(
        public string $val
    ) {}
}

class Bar
{
    public function __construct(
        public Foo $foo
    ) {}
}

class Baz
{
    public function __construct(
        public Bar $bar
    ) {}
}

// Build container pass an array with dependencies definitions
$container = new ServiceContainer([
    // Callable factory
    Foo::class => fn() => new Foo('foo', new \stdClass),
    // Class string autoworing resolution
    Bar::class => Bar::class,
    // Service alias
    'foo.alias' => Foo::class,
    // Interface resolution
    FooInterface::class => Foo::class
]);

$foo = $container->get(Foo::class);
assert($foo instanceof Foo::class);
assert($foo == $container->get('foo.alias'));

$bar = $container->get(Bar::class);
assert($bar instanceof Bar::class);
assert($bar->foo === $foo);

$service = $container->get(FooInterface::class);
assert($service instanceof FooInterface::class);
assert($foo == $service);

// Autowiring non-defined service
$baz = $container->get(Baz::class);
assert($baz instanceof Baz::class);
assert($baz->bar === $bar);
```
