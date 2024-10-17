<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\phpunit\Util;

use Iterator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Paysera\DeliverySdk\Exception\BaseException;
use Paysera\DeliverySdk\Exception\ContainerException;
use Paysera\DeliverySdk\Exception\ContainerNotFoundException;
use Paysera\DeliverySdk\Tests\Fixtures\ClassWithNotTypedArgument;
use Paysera\DeliverySdk\Tests\Fixtures\ClassWithNotTypedArgumentHasDefaultValue;
use Paysera\DeliverySdk\Util\Container;
use Paysera\DeliverySdk\Util\DeliveryGatewayUtils;

class ContainerTest extends MockeryTestCase
{
    protected ?Container $container = null;

    public function mockeryTestSetUp(): void
    {
        parent::mockeryTestSetUp();
        $this->container = new Container();
    }

    public function mockeryTestTearDown(): void
    {
        parent::mockeryTestTearDown();

        m::close();
    }

    public function testHasNotId(): void
    {
        $this->assertFalse($this->container->has(DeliveryGatewayUtils::class));
    }

    public function testHasId(): void
    {
        $this->container->get(DeliveryGatewayUtils::class);

        $this->assertTrue($this->container->has(DeliveryGatewayUtils::class));
    }

    public function testSet(): void
    {
        $this->assertFalse($this->container->has(DeliveryGatewayUtils::class));

        $this->container->set(DeliveryGatewayUtils::class, new DeliveryGatewayUtils());

        $this->assertTrue($this->container->has(DeliveryGatewayUtils::class));
    }

    public function testGet(): void
    {
        $this->assertFalse($this->container->has(DeliveryGatewayUtils::class));
        $this->assertInstanceOf(DeliveryGatewayUtils::class, $this->container->get(DeliveryGatewayUtils::class));
        $this->assertTrue($this->container->has(DeliveryGatewayUtils::class));
    }

    public function testGetBuildException(): void
    {
        $this->expectException(ContainerNotFoundException::class);
        $this->expectExceptionMessage('Service with id `' . ClassWithNotTypedArgument::class . '` not found in container');

        $this->container->get(ClassWithNotTypedArgument::class);
    }

    public function testBuildSimpleObject(): void
    {
        $this->assertFalse($this->container->has(DeliveryGatewayUtils::class));

        $this->assertNotSame(
            $this->container->get(DeliveryGatewayUtils::class),
            $this->container->build(DeliveryGatewayUtils::class)
        );
    }

    public function testBuildClassDoesNotExistException(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionCode(BaseException::E_CONTAINER);

        $this->container->build('test');
    }

    public function testBuildClassIsNotInstantiableException(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionCode(BaseException::E_CONTAINER);

        $this->container->build(Iterator::class);
    }

    public function testBuildWithConstructorArgumentsWithoutType(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Can not resolve class dependency input');

        $this->container->build(ClassWithNotTypedArgument::class);
    }

    public function testBuildWithConstructorArgumentsWithoutTypeHasDefaultValue(): void
    {
        /** @var ClassWithNotTypedArgumentHasDefaultValue $object */
        $object = $this->container->build(ClassWithNotTypedArgumentHasDefaultValue::class);

        $this->assertEquals(['test'], $object->array, 'Object property must be set by default value.');
    }
}