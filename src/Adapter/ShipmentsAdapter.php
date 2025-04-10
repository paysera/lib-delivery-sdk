<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Adapter;

use Paysera\DeliveryApi\MerchantClient\Entity\ShipmentCreate;
use Paysera\DeliverySdk\Collection\OrderItemsCollection;
use Paysera\DeliverySdk\Entity\MerchantOrderItemInterface;

class ShipmentsAdapter
{
    /**
     * @param OrderItemsCollection<MerchantOrderItemInterface> $items
     * @return iterable<ShipmentCreate>
     */
    public function convert(OrderItemsCollection $items): iterable
    {
        foreach ($items as $item) {
            yield (new ShipmentCreate())
                ->setHeight($item->getHeight())
                ->setWidth($item->getWidth())
                ->setLength($item->getLength())
                ->setWeight($item->getWeight())
            ;
        }
    }
}
