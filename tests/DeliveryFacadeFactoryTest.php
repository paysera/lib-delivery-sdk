<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests;

use Paysera\DeliverySdk\DeliveryFacadeFactory;
use Paysera\DeliverySdk\Repository\DeliveryGatewayRepositoryInterface;
use Paysera\DeliverySdk\Repository\MerchantOrderRepositoryInterface;
use Paysera\DeliverySdk\Service\DeliveryLoggerInterface;
use Paysera\DeliverySdk\Service\MerchantOrderLoggerInterface;
use Paysera\DeliverySdk\Util\Container;
use PHPUnit\Framework\TestCase;

class DeliveryFacadeFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $container = (new Container())
            ->set(
                MerchantOrderRepositoryInterface::class,
                $this->createMock(MerchantOrderRepositoryInterface::class)
            )
            ->set(
                DeliveryLoggerInterface::class,
                $this->createMock(DeliveryLoggerInterface::class)
            )
            ->set(
                MerchantOrderLoggerInterface::class,
                $this->createMock(MerchantOrderLoggerInterface::class)
            )
            ->set(
                DeliveryGatewayRepositoryInterface::class,
                $this->createMock(DeliveryGatewayRepositoryInterface::class)
            )
        ;

        $facade = (new DeliveryFacadeFactory($container))->create();

        $this->assertNotNull(
            $facade,
            'Method must return object of ' . DeliveryFacadeFactory::class . ' class.'
        );
    }
}
