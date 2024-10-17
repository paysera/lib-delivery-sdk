<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\phpunit\Adapter;

use Paysera\DeliveryApi\MerchantClient\Entity\OrderCreate;
use Paysera\DeliveryApi\MerchantClient\Entity\ShipmentCreate;
use Paysera\DeliveryApi\MerchantClient\Entity\ShipmentPointCreate;
use Paysera\DeliverySdk\Adapter\OrderCreateRequestAdapter;
use Paysera\DeliverySdk\Adapter\OrderNotificationAdapter;
use Paysera\DeliverySdk\Adapter\ShipmentPointAdapter;
use Paysera\DeliverySdk\Adapter\ShipmentsAdapter;
use Paysera\DeliverySdk\Collection\OrderItemsCollection;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\MerchantOrderPartyInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryGatewaySettingsInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Entity\PayseraDeliverySettingsInterface;
use Paysera\DeliverySdk\Util\DeliveryGatewayUtils;
use PHPUnit\Framework\TestCase;

class OrderCreateRequestAdapterTest extends TestCase
{
    private OrderCreateRequestAdapter $orderCreateRequestAdapter;
    private ShipmentsAdapter $shipmentsAdapterMock;
    private ShipmentPointAdapter $shipmentPointAdapterMock;
    private PayseraDeliveryOrderRequest $requestMock;
    private MerchantOrderInterface $orderDtoMock;
    private PayseraDeliverySettingsInterface $deliverySettingsMock;
    private PayseraDeliveryGatewaySettingsInterface $deliveryGatewaySettingsMock;

    protected function setUp(): void
    {
        $this->shipmentsAdapterMock = $this->createMock(ShipmentsAdapter::class);
        $notificationCallbackAdapterMock = $this->createMock(OrderNotificationAdapter::class);
        $this->shipmentPointAdapterMock = $this->createMock(ShipmentPointAdapter::class);
        $this->requestMock = $this->createMock(PayseraDeliveryOrderRequest::class);
        $this->orderDtoMock = $this->createMock(MerchantOrderInterface::class);
        $this->deliverySettingsMock = $this->createMock(PayseraDeliverySettingsInterface::class);
        $this->deliveryGatewaySettingsMock = $this->createMock(PayseraDeliveryGatewaySettingsInterface::class);

        $this->orderCreateRequestAdapter = new OrderCreateRequestAdapter(
            $this->shipmentsAdapterMock,
            $notificationCallbackAdapterMock,
            $this->shipmentPointAdapterMock,
            new DeliveryGatewayUtils()
        );
    }

    public function testConvert(): void
    {
        $this->requestMock
            ->method('getOrder')
            ->willReturn($this->orderDtoMock)
        ;
        $this->requestMock
            ->method('getDeliveryGatewayCode')
            ->willReturn('gatewayCode')
        ;
        $this->requestMock
            ->method('getDeliveryGatewaySettings')
            ->willReturn($this->deliveryGatewaySettingsMock)
        ;
        $this->requestMock
            ->method('getDeliverySettings')
            ->willReturn($this->deliverySettingsMock)
        ;

        $this->orderDtoMock
            ->method('getItems')
            ->willReturn(new OrderItemsCollection())
        ;
        $this->orderDtoMock
            ->method('getShipping')
            ->willReturn($this->createMock(MerchantOrderPartyInterface::class))
        ;
        $this->orderDtoMock->method('getNumber')->willReturn('ORDER123');
        $this->deliverySettingsMock->method('getResolvedProjectId')->willReturn('123');

        $this->shipmentsAdapterMock
            ->method('convert')
            ->willReturn([new ShipmentCreate()])
        ;

        $this->shipmentPointAdapterMock
            ->method('convert')
            ->willReturn(new ShipmentPointCreate())
        ;

        $order = $this->orderCreateRequestAdapter->convert($this->requestMock);

        $this->assertInstanceOf(OrderCreate::class, $order);
        $this->assertSame('gatewayCode', $order->getShipmentGatewayCode());
        $this->assertSame('ORDER123', $order->getEshopOrderId());
        $this->assertSame('123', $order->getProjectId());
    }

    public function testConvertWithoutOptionalFields(): void
    {
        $this->requestMock
            ->method('getOrder')
            ->willReturn($this->orderDtoMock)
        ;
        $this->requestMock
            ->method('getDeliveryGatewayCode')
            ->willReturn('gatewayCode')
        ;
        $this->requestMock
            ->method('getDeliveryGatewaySettings')
            ->willReturn($this->deliveryGatewaySettingsMock)
        ;
        $this->requestMock
            ->method('getDeliverySettings')
            ->willReturn($this->deliverySettingsMock)
        ;

        $this->orderDtoMock
            ->method('getItems')
            ->willReturn(new OrderItemsCollection())
        ;
        $this->orderDtoMock
            ->method('getShipping')
            ->willReturn($this->createMock(MerchantOrderPartyInterface::class))
        ;
        $this->orderDtoMock
            ->method('getNumber')
            ->willReturn('ORDER123')
        ;
        $this->deliverySettingsMock
            ->method('getResolvedProjectId')
            ->willReturn(null)
        ;
        $this->orderDtoMock
            ->method('getNotificationCallback')
            ->willReturn(null)
        ;

        $order = $this->orderCreateRequestAdapter->convert($this->requestMock);

        $this->assertNull($order->getProjectId());
        $this->assertNull($order->getOrderNotification());
    }
}
