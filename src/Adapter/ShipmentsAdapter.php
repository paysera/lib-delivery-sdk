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
    public function convert(
        OrderItemsCollection $items,
        bool $isSinglePerOrderShipmentEnabled = false
    ): iterable {
        if ($isSinglePerOrderShipmentEnabled) {
            return $this->createSingleShipment($items);
        }
        return $this->createMultipleShipments($items);
    }

    /**
     * @param OrderItemsCollection<MerchantOrderItemInterface> $items
     * @return iterable<ShipmentCreate>
     */
    private function createSingleShipment(OrderItemsCollection $items): iterable
    {
        $width = 0;
        $height = 0;
        $length = 0;
        $weight = 0;

        foreach ($items as $item) {
            $width += $item->getWidth();
            $height += $item->getHeight();
            $length += $item->getLength();
            $weight += $item->getWeight();
        }

        return [
            (new ShipmentCreate())
                ->setLength((int) ceil($length))
                ->setWidth((int) ceil($width))
                ->setHeight((int) ceil($height))
                ->setWeight((int) ceil($weight))
        ];
    }

    /**
     * @param OrderItemsCollection<MerchantOrderItemInterface> $items
     * @return iterable<ShipmentCreate>
     */
    private function createMultipleShipments(OrderItemsCollection $items): iterable
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
