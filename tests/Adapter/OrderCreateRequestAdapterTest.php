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
use Paysera\DeliverySdk\Entity\PayseraDeliveryGatewayInterface;
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
    private MerchantOrderInterface $merchantOrderMock;
    private PayseraDeliveryGatewayInterface $deliveryGatewayMock;
    private PayseraDeliverySettingsInterface $deliverySettingsMock;
    private PayseraDeliveryGatewaySettingsInterface $deliveryGatewaySettingsMock;

    protected function setUp(): void
    {
        $this->shipmentsAdapterMock = $this->createMock(ShipmentsAdapter::class);
        $notificationCallbackAdapterMock = $this->createMock(OrderNotificationAdapter::class);
        $this->shipmentPointAdapterMock = $this->createMock(ShipmentPointAdapter::class);
        $this->requestMock = $this->createMock(PayseraDeliveryOrderRequest::class);
        $this->merchantOrderMock = $this->createMock(MerchantOrderInterface::class);
        $this->deliveryGatewayMock = $this->createMock(PayseraDeliveryGatewayInterface::class);
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
            ->willReturn($this->merchantOrderMock)
        ;

        $this->requestMock
            ->method('getDeliverySettings')
            ->willReturn($this->deliverySettingsMock)
        ;

        $this->merchantOrderMock
            ->method('getItems')
            ->willReturn(new OrderItemsCollection())
        ;
        $this->merchantOrderMock
            ->method('getShipping')
            ->willReturn($this->createMock(MerchantOrderPartyInterface::class))
        ;
        $this->merchantOrderMock
            ->method('getDeliveryGateway')
            ->willReturn($this->deliveryGatewayMock)
        ;
        $this->deliveryGatewayMock
            ->method('getSettings')
            ->willReturn($this->deliveryGatewaySettingsMock)
        ;
        $this->deliveryGatewayMock
            ->method('getCode')
            ->willReturn('gatewayCode')
        ;
        $this->merchantOrderMock->method('getNumber')->willReturn('ORDER123');

        $this->merchantOrderMock
            ->method('getTestMode')
            ->willReturn(true)
        ;

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
            ->willReturn($this->merchantOrderMock)
        ;

        $this->requestMock
            ->method('getDeliverySettings')
            ->willReturn($this->deliverySettingsMock)
        ;

        $this->merchantOrderMock
            ->method('getItems')
            ->willReturn(new OrderItemsCollection())
        ;
        $this->merchantOrderMock
            ->method('getShipping')
            ->willReturn($this->createMock(MerchantOrderPartyInterface::class))
        ;
        $this->merchantOrderMock
            ->method('getNumber')
            ->willReturn('ORDER123')
        ;
        $this->deliverySettingsMock
            ->method('getResolvedProjectId')
            ->willReturn(null)
        ;
        $this->merchantOrderMock
            ->method('getNotificationCallback')
            ->willReturn(null)
        ;
        $this->merchantOrderMock
            ->method('getDeliveryGateway')
            ->willReturn($this->deliveryGatewayMock)
        ;
        $this->deliveryGatewayMock
            ->method('getSettings')
            ->willReturn($this->deliveryGatewaySettingsMock)
        ;
        $this->deliveryGatewayMock
            ->method('getCode')
            ->willReturn('gatewayCode')
        ;

        $order = $this->orderCreateRequestAdapter->convert($this->requestMock);

        $this->assertNull($order->getProjectId());
        $this->assertNull($order->getOrderNotification());
    }
}
