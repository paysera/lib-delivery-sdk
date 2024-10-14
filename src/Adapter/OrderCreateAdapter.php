<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Adapter;

use Paysera\DeliveryApi\MerchantClient\Entity\OrderCreate;
use Paysera\DeliveryApi\MerchantClient\Entity\OrderUpdate;
use Paysera\Dto\EshopOrderDto;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;

class OrderCreateAdapter
{
    private ShipmentsAdapter $shipmentsAdapter;
    private OrderNotificationAdapter $notificationCallbackAdapter;
    private ShipmentPointAdapter $shipmentPointAdapter;

    public function __construct(
        ShipmentsAdapter $shipmentsAdapter,
        OrderNotificationAdapter $notificationCallbackAdapter,
        ShipmentPointAdapter $shipmentPointAdapter
    ) {
        $this->shipmentsAdapter = $shipmentsAdapter;
        $this->notificationCallbackAdapter = $notificationCallbackAdapter;
        $this->shipmentPointAdapter = $shipmentPointAdapter;
    }

    public function convert(MerchantOrderInterface $orderDto): OrderCreate
    {
        $order = (new OrderCreate())
            ->setShipments([...$this->shipmentsAdapter->adapt($orderDto->getItems())])
            ->setReceiver($this->shipmentPointAdapter->convert($orderDto->getShipping()))
            ->setEshopOrderId($orderDto->getNumber())
        ;

        if ($orderDto->getNotificationCallback() !== null) {
            $order->setOrderNotification(
                $this->notificationCallbackAdapter->convert($orderDto->getNotificationCallback())
            );
        }

        return $order;
    }
}
