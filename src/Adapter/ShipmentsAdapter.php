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
        $totalWeight = 0.0;
        $maxLength = 0.0;
        $maxWidth = 0.0;
        $totalHeight = 0.0;

        foreach ($items as $item) {
            $totalWeight += $item->getWeight();
            $totalHeight += $item->getHeight();
            $maxLength = max($maxLength, $item->getLength());
            $maxWidth = max($maxWidth, $item->getWidth());
        }

        return [
            (new ShipmentCreate())
                ->setLength($maxLength)
                ->setWidth($maxWidth)
                ->setHeight($totalHeight)
                ->setWeight($totalWeight),
        ];
    }
}
