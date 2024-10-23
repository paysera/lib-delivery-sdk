<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\Client;

use Exception;
use Paysera\DeliveryApi\MerchantClient\Entity\Order;
use Paysera\DeliverySdk\Client\DeliveryApiClient;
use Paysera\DeliverySdk\Client\DeliveryOrderApiClient;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Exception\DeliveryOrderRequestException;
use Paysera\DeliverySdk\Service\DeliveryLoggerInterface;
use PHPUnit\Framework\TestCase;

class DeliveryApiClientTest extends TestCase
{
    private DeliveryOrderApiClient $orderRequestHandlerMock;
    private DeliveryLoggerInterface $loggerMock;
    private PayseraDeliveryOrderRequest $deliveryOrderRequestMock;
    private Order $orderMock;
    private DeliveryApiClient $deliveryApiClient;

    protected function setUp(): void
    {
        $this->orderRequestHandlerMock = $this->createMock(DeliveryOrderApiClient::class);
        $this->loggerMock = $this->createMock(DeliveryLoggerInterface::class);
        $this->deliveryOrderRequestMock = $this->createMock(PayseraDeliveryOrderRequest::class);
        $this->orderMock = $this->createMock(Order::class);

        $this->deliveryApiClient = new DeliveryApiClient(
            $this->orderRequestHandlerMock,
            $this->loggerMock
        );
    }

    public function testPostOrder(): void
    {
        $this->orderRequestHandlerMock->expects($this->once())
            ->method('create')
            ->with($this->deliveryOrderRequestMock)
            ->willReturn($this->orderMock)
        ;

        $result = $this->deliveryApiClient->postOrder($this->deliveryOrderRequestMock);

        $this->assertSame($this->orderMock, $result);
    }

    public function testPatchOrder(): void
    {
        $this->orderRequestHandlerMock->expects($this->once())
            ->method('update')
            ->with($this->deliveryOrderRequestMock)
            ->willReturn($this->orderMock)
        ;

        $result = $this->deliveryApiClient->patchOrder($this->deliveryOrderRequestMock);

        $this->assertSame($this->orderMock, $result);
    }

    public function testGetOrder(): void
    {
        $this->orderRequestHandlerMock->expects($this->once())
            ->method('get')
            ->with($this->deliveryOrderRequestMock)
            ->willReturn($this->orderMock)
        ;

        $result = $this->deliveryApiClient->getOrder($this->deliveryOrderRequestMock);

        $this->assertSame($this->orderMock, $result);
    }

    public function testPostOrderThrowsException(): void
    {
        $orderMock = $this->createMock(MerchantOrderInterface::class);
        $this->deliveryOrderRequestMock->method('getOrder')
            ->willReturn($orderMock)
        ;

        $orderMock->method('getNumber')->willReturn('12345');

        $this->orderRequestHandlerMock->method('create')
            ->willThrowException(new Exception('Error creating order'))
        ;

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                "Cannot perform operation 'create' on delivery order for order id 12345.",
                $this->isInstanceOf(Exception::class)
            )
        ;

        $this->expectException(DeliveryOrderRequestException::class);

        $this->deliveryApiClient->postOrder($this->deliveryOrderRequestMock);
    }
}
