# Micro Container

Micro Container is a lightweight PHP Dependency Injection Container designed to simplify the management of dependencies in your PHP applications. With Micro Container, you can easily define and resolve dependencies using a clean and intuitive syntax.

## Installation

To install Micro Container, use [Composer](https://getcomposer.org/):

```bash
composer require marcelomx/micro-container
```

## Usage

### Basic Setup

Start by creating an instance of the `ServiceContainer` class and provide it with an array containing dependency definitions:

```php
use MicroContainer\ServiceContainer;

$container = new ServiceContainer([
    Foo::class => fn() => new Foo('foo', new \stdClass),
    Bar::class => Bar::class,
    'foo.alias' => Foo::class,
    FooInterface::class => Foo::class
]);
```

Here's a breakdown of the definitions:

-   **Callable Factory (`Foo::class`):** Using a callable factory function to define the instantiation of the `Foo` class, allowing for custom initialization.

-   **Class String Autowiring (`Bar::class`):** Demonstrating class string autowiring resolution for the `Bar` class, where Micro Container automatically resolves dependencies.

-   **Service Alias (`'foo.alias'`):** Creating an alias for the `Foo` class to demonstrate the usage of service aliases in the container.

-   **Interface Resolution (`FooInterface::class`):** Resolving an interface (`FooInterface`) to its implementation (`Foo`).

### Retrieving Services

Once the container is set up, you can easily retrieve instances of your defined services:

```php
$foo = $container->get(Foo::class);
assert($foo instanceof Foo);

$aliasFoo = $container->get('foo.alias');
assert($aliasFoo === $foo);

$bar = $container->get(Bar::class);
assert($bar instanceof Bar);
assert($bar->foo === $foo);

$service = $container->get(FooInterface::class);
assert($service instanceof FooInterface);
assert($service === $foo);
```

### Autowiring

Micro Container supports autowiring for non-defined services. In the example below, the `Baz` class is created without explicitly defining it in the container:

```php
$baz = $container->get(Baz::class);
assert($baz instanceof Baz);
assert($baz->bar === $bar);
```

This showcases Micro Container's ability to automatically resolve dependencies, making it convenient to work with classes that are not explicitly defined in the container.

Additionally, you can use the Autowired attribute to automatically inject dependencies into your class properties:

```php
use MicroContainer\Attributes\Autowired;

class MyClass {
    #[Autowired()]
    private FooInterface $foo;

    #[Autowired(service: Bar::class)]
    private $bar;
}
```

In this example, the Autowired attribute is utilized to indicate that the $foo property should be automatically injected with an instance of FooInterface, and the $bar property should be injected with an instance of the Bar class. This provides a concise and declarative way to express dependencies within your class, improving readability and reducing boilerplate code.

Feel free to customize and expand on this basic setup to meet the specific requirements of your project.

## Copyright

Micro Container is open-source software released under the [MIT License](LICENSE). © [Your Name or Organization].

## Contribution

We welcome contributions! To contribute to Micro Container, please follow our [contribution guidelines](CONTRIBUTING.md). Your help is highly appreciated.
