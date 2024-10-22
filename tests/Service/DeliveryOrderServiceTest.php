<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\Service;

use Paysera\DeliveryApi\MerchantClient\Entity\Order;
use Paysera\DeliverySdk\Client\DeliveryApiClient;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Entity\PayseraDeliverySettingsInterface;
use Paysera\DeliverySdk\Repository\MerchantOrderRepositoryInterface;
use Paysera\DeliverySdk\Service\DeliveryLoggerInterface;
use Paysera\DeliverySdk\Service\DeliveryOrderService;
use PHPUnit\Framework\TestCase;

class DeliveryOrderServiceTest extends TestCase
{
    private DeliveryOrderService $service;
    private MerchantOrderRepositoryInterface $merchantOrderRepository;
    private DeliveryApiClient $deliveryApiClient;
    private DeliveryLoggerInterface $logger;

    protected function setUp(): void
    {
        $this->merchantOrderRepository = $this->createMock(MerchantOrderRepositoryInterface::class);
        $this->deliveryApiClient = $this->createMock(DeliveryApiClient::class);
        $this->logger = $this->createMock(DeliveryLoggerInterface::class);

        $this->service = new DeliveryOrderService(
            $this->merchantOrderRepository,
            $this->deliveryApiClient,
            $this->logger
        );
    }

    public function testCreateDeliveryOrder(): void
    {
        $order = $this->createMock(MerchantOrderInterface::class);
        $deliveryOrderRequest = $this->createMock(PayseraDeliveryOrderRequest::class);

        $deliveryOrderRequest->method('getOrder')->willReturn($order);

        $settingsMock = $this->createMock(PayseraDeliverySettingsInterface::class);
        $settingsMock->method('getProjectId')->willReturn(123);

        $deliveryOrderRequest->method('getDeliverySettings')->willReturn($settingsMock);

        $deliveryOrder = $this->createMock(Order::class);
        $deliveryOrder->method('getId')->willReturn('1');
        $deliveryOrder->method('getNumber')->willReturn('order123');

        $this->deliveryApiClient->expects($this->once())
            ->method('postOrder')
            ->with($deliveryOrderRequest)
            ->willReturn($deliveryOrder);

        $order->expects($this->once())
            ->method('setDeliveryOrderId')
            ->with('1');

        $order->expects($this->once())
            ->method('setDeliveryOrderNumber')
            ->with('order123');

        $this->merchantOrderRepository->expects($this->once())
            ->method('save')
            ->with($order);

        $this->logger->expects($this->exactly(2))
            ->method('info');

        $result = $this->service->createDeliveryOrder($deliveryOrderRequest);

        $this->assertSame($order, $result);
    }

    public function testUpdateDeliveryOrder(): void
    {
        $order = $this->createMock(MerchantOrderInterface::class);
        $deliveryOrderRequest = $this->createMock(PayseraDeliveryOrderRequest::class);

        $deliveryOrderRequest->method('getOrder')->willReturn($order);

        $deliveryOrder = $this->createMock(Order::class);

        $this->deliveryApiClient->expects($this->once())
            ->method('patchOrder')
            ->with($deliveryOrderRequest)
            ->willReturn($deliveryOrder);

        $this->logger->expects($this->exactly(2))
            ->method('info');

        $result = $this->service->updateDeliveryOrder($deliveryOrderRequest);

        $this->assertSame($order, $result);
    }
}
