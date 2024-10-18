<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk;

use Paysera\DeliverySdk\Service\DeliveryOrderCallbackService;
use Paysera\DeliverySdk\Service\DeliveryOrderService;
use Paysera\DeliverySdk\Util\Container;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class DeliveryFacadeFactory
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return DeliveryFacade
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function create(): DeliveryFacade
    {
        $container = $container ?? new Container();

        return new DeliveryFacade(
            $this->container->get(DeliveryOrderService::class),
            $this->container->get(DeliveryOrderCallbackService::class)
        );
    }
}