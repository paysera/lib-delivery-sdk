<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Adapter;

use Paysera\DeliveryApi\MerchantClient\Entity\OrderUpdate;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Utils\DeliveryGatewayUtils;

class OrderUpdaterequestAdapter
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

    public function convert(PayseraDeliveryOrderRequest $request): OrderUpdate
    {
        $orderDto = $request->getOrder();

        $order = (new OrderUpdate())
            ->setShipmentGatewayCode(
                DeliveryGatewayUtils::resolveDeliveryGatewayCode($request->getDeliveryGatewayCode())
            )
            ->setShipmentMethodCode(
                DeliveryGatewayUtils::getShipmentMethodCode($request->getDeliveryGatewaySettings())
            )
            ->setShipments([...$this->shipmentsAdapter->adapt($orderDto->getItems())])
            ->setReceiver($this->shipmentPointAdapter->convert($orderDto->getShipping()))
            ->setEshopOrderId($orderDto->getNumber())
        ;

        $projectId = $request->getDeliverySettings()->getResolvedProjectId();

        if ($projectId !== null) {
            $order->setProjectId($projectId);
        }

        if ($orderDto->getNotificationCallback() !== null) {
            $order->setOrderNotification(
                $this->notificationCallbackAdapter->convert($orderDto->getNotificationCallback())
            );
        }

        return $order;
    }
}
