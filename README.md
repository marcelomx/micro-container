# Micro Container

A micro PHP Dependency Injection Container.

## Installation

```bash
composer install marcelomx/micro-container
```

## Usage

```php

use MicroContainer\Container;

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
$container = new Container([
    'foo.value' => 'Some value',
    Foo::class => fn($c) => new Foo($c['foo.value']),
    Bar::class => fn($c) => new Bar($c->get(Foo::class)),
    // Auto wiring
    Baz::class // Baz depends on Bar
    // Service identification (alias)
    'foo.alias' => Foo::class,
    // Interface resolution
    FooInterface::class => Foo::class
]);

$foo = $container->get(Foo::class);
$bar = $container->get(Bar::class);
$baz = $container->get(Baz::class);

// prints "Some value"
echo $foo->value;

if ($foo->value === $container->get('foo.value')) {
    echo 'Yes, its the same value' . PHP_EOL;
}

if ($bar->foo === $foo) {
    echo 'Yes, is same instance' . PHP_EOL;
}

if ($baz->bar === $bar) {
    echo 'Yes, autowiring is working' . PHP_EOL;
}

if ($foo === $container->get('foo.alias')) {
    echo 'Foo alias is same foo' . PHP_EOL;
}

if ($foo === $container->get(FooInterface::class)) {
    echo 'FooInterface is foo too' . PHP_EOL;
}


```
