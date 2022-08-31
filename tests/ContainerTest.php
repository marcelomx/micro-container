<?php

declare(strict_types=1);

namespace MicroContainer\Tests;

use MicroContainer\Container;
use MicroContainer\Tests\Stubs\Inner\Deep\Dependency;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ContainerTest extends TestCase
{
    function testNotFoundDefs()
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $container = new Container();
        $container->get('invalidDefinition');
    }

    function testBuildContainerRawValues()
    {
        $container = new Container([
            'foo' => 1,
            'bar' => 2
        ]);

        $this->assertEquals(1, $container->get('foo'));
        $this->assertEquals(2, $container->get('bar'));
    }

    function testBuildContainerClassDefs()
    {
        $container = new Container([
            'foo' => 'foostring',
            \stdClass::class => fn () => new \stdClass,
            Foo::class => fn (Container $c) => new Foo($c->get('foo'), $c->get(\stdClass::class)),
            Bar::class => fn (Container $c) => new Bar($c->get(Foo::class))
        ]);

        $fooValue = $container->get('foo');
        $stdDep   = $container->get(\stdClass::class);
        $fooObj   = $container->get(Foo::class);
        $barObj   = $container->get(Bar::class);

        $this->assertEquals('foostring', $fooValue);
        $this->assertInstanceOf(\stdClass::class, $stdDep);
        $this->assertInstanceOf(Foo::class, $fooObj);
        $this->assertEquals($fooValue, $fooObj->foo);
        $this->assertSame($stdDep, $fooObj->stdDep);
        $this->assertInstanceOf(Bar::class, $barObj);
        $this->assertSame($fooObj, $barObj->fooDep);
    }

    function testBuildDepsWithAutowiring()
    {
        $container = new Container([
            'foo' => 'foostring',
            \stdClass::class => fn () => new \stdClass,
            Foo::class => Foo::class,
            'foo.service' => fn (Container $c) => $c->get(Foo::class),
            FooInterface::class => Foo::class,
            Bar::class => Bar::class,
            Baz::class => Baz::class
        ]);

        $fooValue = $container->get('foo');
        $stdDep   = $container->get(\stdClass::class);
        $fooObj   = $container->get(FooInterface::class);
        $barObj   = $container->get(Bar::class);
        $bazObj   = $container->get(Baz::class);

        $this->assertEquals('foostring', $fooValue);
        $this->assertInstanceOf(\stdClass::class, $stdDep);
        $this->assertInstanceOf(Foo::class, $fooObj);
        $this->assertEquals($fooValue, $fooObj->foo);
        $this->assertSame($stdDep, $fooObj->stdDep);
        $this->assertInstanceOf(Bar::class, $barObj);
        $this->assertNotSame($fooObj, $barObj->fooDep);
        $this->assertInstanceOf(Baz::class, $bazObj);
    }

    function testContainerAsSelfDependency()
    {
        $container = new Container([
            'container.dep' => ContainerDep::class
        ]);

        $service = $container->get('container.dep');
        $this->assertSame($container, $service->container);
    }
}

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
    public function __construct(public Foo $fooDep)
    {
    }
}


class Baz
{
    public function __construct(
        public string $name = 'Default Value',
        public Foo $foo,
        public Bar $bar,
        public Dependency $dependency
    ) {
    }
}

interface FooInterface
{
}

class ContainerDep
{
    public function __construct(public ContainerInterface $container)
    {
    }
}
