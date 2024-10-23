<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\phpunit\Adapter;

use Paysera\DeliveryApi\MerchantClient\Entity\OrderCreate;
use Paysera\DeliveryApi\MerchantClient\Entity\OrderUpdate;
use Paysera\DeliverySdk\Adapter\DeliveryOrderRequestAdapterFacade;
use Paysera\DeliverySdk\Adapter\OrderCreateRequestAdapter;
use Paysera\DeliverySdk\Adapter\OrderUpdateRequestAdapter;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use PHPUnit\Framework\TestCase;

class DeliveryOrderRequestAdapterFacadeTest extends TestCase
{
    private DeliveryOrderRequestAdapterFacade $facade;
    private OrderCreateRequestAdapter $createAdapterMock;
    private OrderUpdateRequestAdapter $updateAdapterMock;
    private PayseraDeliveryOrderRequest $requestMock;

    protected function setUp(): void
    {
        $this->createAdapterMock = $this->createMock(OrderCreateRequestAdapter::class);
        $this->updateAdapterMock = $this->createMock(OrderUpdateRequestAdapter::class);
        $this->facade = new DeliveryOrderRequestAdapterFacade(
            $this->createAdapterMock,
            $this->updateAdapterMock
        );
        $this->requestMock = $this->createMock(PayseraDeliveryOrderRequest::class);
    }

    public function testConvertCreate(): void
    {
        $orderCreateMock = $this->createMock(OrderCreate::class);
        $this->createAdapterMock->method('convert')->willReturn($orderCreateMock);

        $result = $this->facade->convertCreate($this->requestMock);

        $this->assertSame($orderCreateMock, $result);
        $this->assertInstanceOf(OrderCreate::class, $result);
    }

    public function testConvertUpdate(): void
    {
        $orderUpdateMock = $this->createMock(OrderUpdate::class);
        $this->updateAdapterMock->method('convert')->willReturn($orderUpdateMock);

        $result = $this->facade->convertUpdate($this->requestMock);

        $this->assertSame($orderUpdateMock, $result);
        $this->assertInstanceOf(OrderUpdate::class, $result);
    }
}
