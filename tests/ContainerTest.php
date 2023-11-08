<?php

declare(strict_types=1);

namespace MicroContainer\Tests;

use MicroContainer\Container;
use MicroContainer\NotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;

require_once __DIR__ . '/stubs.php';

class ContainerTest extends TestCase
{
    public function testHasEntry()
    {
        $container = new Container([Foo::class => Foo::class]);
        $this->assertTrue($container->has(Foo::class));
        $this->assertFalse($container->has('foo'));
    }

    public function testThrowsExceptionWhenEntryNotFound()
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage("No entry was found for 'foo.class' identifier");
        $container = new Container();
        $container->get('foo.class');
    }

    public function testThrowsExceptionWhenPrimitiveNotHasDefaultValue()
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $container = new Container();
        $container->get(Foo::class);
    }

    public function testShouldResolveWithDefinition()
    {
        $container = new Container([
            Foo::class => fn () => new Foo('fooString', new \stdClass),
            'foo.alias' => Foo::class
        ]);

        $this->assertInstanceOf(Foo::class, $service = $container->get(Foo::class));
        $this->assertSame($service, $container->get('foo.alias'));
    }

    public function testThrowsExceptionWhenInterfaceHasNoDefinition()
    {
        $expectedMessage = "Target '" . FooInterface::class . "' is not instantiable";

        try {
            $container = new Container();
            $container->get(FooInterface::class);
        } catch (NotFoundException $e) {
            $this->assertEquals($expectedMessage, $e->getPrevious()?->getMessage());
        }
    }

    public function testShouldResolveInterfaceEntry()
    {
        $foo = new Foo('fooString', new \stdClass);
        $container = new Container([
            Foo::class => fn () => $foo,
            FooInterface::class => Foo::class
        ]);

        $service = $container->get(FooInterface::class);
        $this->assertInstanceOf(FooInterface::class, $service);
        $this->assertSame($service, $foo);
    }

    public function testShouldResolveWithClassString()
    {
        $foo = new Foo('fooString', new \stdClass);
        $container = new Container([
            Foo::class => fn () => $foo,
            Bar::class => Bar::class
        ]);

        $service = $container->get(Bar::class);
        $this->assertInstanceOf(Bar::class, $service);
        $this->assertSame($foo, $service->foo);
    }

    public function testShouldResolveContainerAware()
    {
        $container = new Container([ContainerAware::class => ContainerAware::class]);
        $service = $container->get(ContainerAware::class);
        $this->assertSame($container, $service->container);
    }

    public function testShouldResolveWithAutowiring()
    {
        $foo = new Foo('fooString', new \stdClass);
        $container = new Container([
            Foo::class => fn () => $foo,
            FooInterface::class => Foo::class
        ]);

        $service = $container->get(Baz::class);
        $this->assertInstanceOf(Baz::class, $service);
        $this->assertSame($foo, $service->foo);
        $this->assertSame($container, $service->containerAware->container);
        $this->assertSame($service->containerAware, $container->get(ContainerAware::class));
        $this->assertInstanceOf(Bar::class, $service->bar);
        $this->assertSame($service->bar, $container->get(Bar::class));
    }
}
