<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk;

use Paysera\DeliverySdk\Service\DeliveryOrderCallbackService;
use Paysera\DeliverySdk\Service\DeliveryOrderService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class DeliveryFacadeFactory
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
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
        return new DeliveryFacade(
            $this->container->get(DeliveryOrderService::class),
            $this->container->get(DeliveryOrderCallbackService::class)
        );
    }
}
