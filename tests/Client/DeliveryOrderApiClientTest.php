<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\Client;

use Paysera\DeliveryApi\MerchantClient\Entity\Order;
use Paysera\DeliveryApi\MerchantClient\Entity\OrderCreate;
use Paysera\DeliveryApi\MerchantClient\Entity\OrderUpdate;
use Paysera\DeliveryApi\MerchantClient\MerchantClient;
use Paysera\DeliverySdk\Adapter\DeliveryOrderRequestAdapterFacade;
use Paysera\DeliverySdk\Client\DeliveryOrderApiClient;
use Paysera\DeliverySdk\Client\Provider\MerchantClientProvider;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Entity\PayseraDeliverySettingsInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeliveryOrderApiClientTest extends TestCase
{
    private DeliveryOrderRequestAdapterFacade $orderRequestAdapterMock;
    private MerchantClientProvider $merchantClientProviderMock;
    private MerchantClient $merchantClientMock;
    private PayseraDeliveryOrderRequest $deliveryOrderRequestMock;
    private Order $orderMock;
    private DeliveryOrderApiClient $deliveryOrderApiClient;

    protected function setUp(): void
    {
        $this->orderRequestAdapterMock = $this->createMock(DeliveryOrderRequestAdapterFacade::class);
        $this->merchantClientProviderMock = $this->createMock(MerchantClientProvider::class);
        $this->merchantClientMock = $this->createMock(MerchantClient::class);
        $this->deliveryOrderRequestMock = $this->createMock(PayseraDeliveryOrderRequest::class);
        $this->orderMock = $this->createMock(Order::class);

        $this->deliveryOrderApiClient = new DeliveryOrderApiClient(
            $this->orderRequestAdapterMock,
            $this->merchantClientProviderMock
        );
    }

    public function testCreate(): void
    {
        $deliverySettingsMock = $this->createMock(PayseraDeliverySettingsInterface::class);
        $orderCreateMock = $this->createMock(OrderCreate::class);

        $this->deliveryOrderRequestMock->method('getDeliverySettings')
            ->willReturn($deliverySettingsMock)
        ;

        $this->orderRequestAdapterMock->method('convertCreate')
            ->with($this->deliveryOrderRequestMock)
            ->willReturn($orderCreateMock);

        $this->merchantClientProviderMock->method('getMerchantClient')
            ->with($deliverySettingsMock)
            ->willReturn($this->merchantClientMock)
        ;

        $this->merchantClientMock->method('createOrder')
            ->with($orderCreateMock)
            ->willReturn($this->orderMock)
        ;

        $result = $this->deliveryOrderApiClient->create($this->deliveryOrderRequestMock);

        $this->assertSame($this->orderMock, $result);
    }

    public function testUpdate(): void
    {
        $deliverySettingsMock = $this->createMock(PayseraDeliverySettingsInterface::class);
        $orderUpdateMock = $this->createMock(OrderUpdate::class);
        $orderDtoMock = $this->createMock(MerchantOrderInterface::class);

        $this->deliveryOrderRequestMock->method('getDeliverySettings')
            ->willReturn($deliverySettingsMock)
        ;

        $this->deliveryOrderRequestMock->method('getOrder')
            ->willReturn($orderDtoMock)
        ;

        $orderDtoMock->method('getDeliveryOrderId')
            ->willReturn('123456')
        ;

        $this->orderRequestAdapterMock->method('convertUpdate')
            ->with($this->deliveryOrderRequestMock)
            ->willReturn($orderUpdateMock)
        ;

        $this->merchantClientProviderMock->method('getMerchantClient')
            ->with($deliverySettingsMock)
            ->willReturn($this->merchantClientMock)
        ;

        $this->merchantClientMock->method('updateOrder')
            ->with('123456', $orderUpdateMock)
            ->willReturn($this->orderMock)
        ;

        $result = $this->deliveryOrderApiClient->update($this->deliveryOrderRequestMock);

        $this->assertSame($this->orderMock, $result);
    }

    public function testPrepaid(): void
    {
        $orderDtoMock = $this->createMock(MerchantOrderInterface::class);

        $this->deliveryOrderRequestMock->method('getOrder')
            ->willReturn($orderDtoMock)
        ;

        $orderDtoMock->method('getDeliveryOrderId')
            ->willReturn('123456')
        ;

        $this->merchantClientProviderMock->method('getMerchantClient')
            ->willReturn($this->merchantClientMock)
        ;

        $this->merchantClientMock->expects($this->once())
            ->method('createOrdersPrepaid')
        ;

        $this->merchantClientMock->method('getOrder')
            ->with('123456')
            ->willReturn($this->orderMock)
        ;

        $result = $this->deliveryOrderApiClient->prepaid($this->deliveryOrderRequestMock);

        $this->assertSame($this->orderMock, $result);
    }

    public function testGet(): void
    {
        $deliverySettingsMock = $this->createMock(PayseraDeliverySettingsInterface::class);
        $orderDtoMock = $this->createMock(MerchantOrderInterface::class);

        $this->deliveryOrderRequestMock->method('getDeliverySettings')
            ->willReturn($deliverySettingsMock)
        ;

        $this->deliveryOrderRequestMock->method('getOrder')
            ->willReturn($orderDtoMock)
        ;

        $orderDtoMock->method('getDeliveryOrderId')
            ->willReturn('654321')
        ;

        $this->merchantClientProviderMock->method('getMerchantClient')
            ->with($deliverySettingsMock)
            ->willReturn($this->merchantClientMock)
        ;

        $this->merchantClientMock->method('getOrder')
            ->with('654321')
            ->willReturn($this->orderMock)
        ;

        $result = $this->deliveryOrderApiClient->get($this->deliveryOrderRequestMock);

        $this->assertSame($this->orderMock, $result);
    }
}
