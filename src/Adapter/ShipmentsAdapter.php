<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Adapter;

use Paysera\DeliveryApi\MerchantClient\Entity\ShipmentCreate;
use Paysera\DeliverySdk\Collection\OrderItemsCollection;
use Paysera\DeliverySdk\Entity\MerchantOrderItemInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliverySettingsInterface;

class ShipmentsAdapter
{
    /**
     * @param OrderItemsCollection<MerchantOrderItemInterface> $items
     * @return iterable<ShipmentCreate>
     */
    public function convert(
        OrderItemsCollection $items,
        PayseraDeliverySettingsInterface $payseraDeliverySettings
    ): iterable {
        if ($payseraDeliverySettings->isSinglePerOrderShipmentEnabled()) {
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
                ->setLength((int) $maxLength)
                ->setWidth((int) $maxWidth)
                ->setHeight((int) $totalHeight)
                ->setWeight((int) $totalWeight),
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
