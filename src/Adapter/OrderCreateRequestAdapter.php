<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Adapter;

use Paysera\DeliveryApi\MerchantClient\Entity\OrderCreate;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Util\DeliveryGatewayUtils;

class OrderCreateRequestAdapter
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

    public function convert(PayseraDeliveryOrderRequest $request): OrderCreate
    {
        $orderDto = $request->getOrder();

        $order = (new OrderCreate())
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
