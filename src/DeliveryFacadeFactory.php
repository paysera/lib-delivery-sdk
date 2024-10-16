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
    /**
     * @return DeliveryFacade
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function create(): DeliveryFacade
    {
        $container = new Container();

        return new DeliveryFacade(
            $container->get(DeliveryOrderService::class),
            $container->get(DeliveryOrderCallbackService::class)
        );
    }
}