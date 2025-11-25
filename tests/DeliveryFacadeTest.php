<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests;

use PHPUnit\Framework\TestCase;
use Paysera\DeliveryApi\MerchantClient\Entity\ProjectCredentials;
use Paysera\DeliverySdk\DeliveryFacade;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Service\DeliveryOrderService;
use Paysera\DeliverySdk\Service\DeliveryOrderCallbackService;
use Paysera\DeliverySdk\Exception\CredentialsValidationException;
use Paysera\DeliverySdk\Exception\DeliveryOrderRequestException;
use Paysera\DeliverySdk\Exception\DeliveryGatewayNotFoundException;

class DeliveryFacadeTest extends TestCase
{
    private DeliveryOrderService $deliveryOrderService;
    private DeliveryOrderCallbackService $deliveryOrderCallbackService;
    private DeliveryFacade $deliveryFacade;

    protected function setUp(): void
    {
        $this->deliveryOrderService = $this->createMock(DeliveryOrderService::class);
        $this->deliveryOrderCallbackService = $this->createMock(DeliveryOrderCallbackService::class);

        $this->deliveryFacade = new DeliveryFacade(
            $this->deliveryOrderService,
            $this->deliveryOrderCallbackService
        );
    }

    public function testCreateDeliveryOrder(): void
    {
        $deliveryOrderRequest = $this->createMock(PayseraDeliveryOrderRequest::class);
        $merchantOrder = $this->createMock(MerchantOrderInterface::class);

        $this->deliveryOrderService
            ->expects($this->once())
            ->method('createDeliveryOrder')
            ->with($deliveryOrderRequest)
            ->willReturn($merchantOrder);

        $result = $this->deliveryFacade->createDeliveryOrder($deliveryOrderRequest);

        $this->assertSame($merchantOrder, $result);
    }

    public function testUpdateDeliveryOrder(): void
    {
        $deliveryOrderRequest = $this->createMock(PayseraDeliveryOrderRequest::class);
        $merchantOrder = $this->createMock(MerchantOrderInterface::class);

        $this->deliveryOrderService
            ->expects($this->once())
            ->method('updateDeliveryOrder')
            ->with($deliveryOrderRequest)
            ->willReturn($merchantOrder);

        $result = $this->deliveryFacade->updateDeliveryOrder($deliveryOrderRequest);

        $this->assertSame($merchantOrder, $result);
    }

    public function testPrepaidDeliveryOrder(): void
    {
        $deliveryOrderRequest = $this->createMock(PayseraDeliveryOrderRequest::class);
        $merchantOrder = $this->createMock(MerchantOrderInterface::class);

        $this->deliveryOrderService ->expects($this->once())
            ->method('prepaidDeliveryOrder')
            ->with($deliveryOrderRequest)
            ->willReturn($merchantOrder)
        ;

        $result = $this->deliveryFacade->prepaidDeliveryOrder($deliveryOrderRequest);

        $this->assertSame($merchantOrder, $result);
    }

    public function testUpdateMerchantOrder(): void
    {
        $deliveryOrderRequest = $this->createMock(PayseraDeliveryOrderRequest::class);
        $merchantOrder = $this->createMock(MerchantOrderInterface::class);

        $this->deliveryOrderCallbackService
            ->expects($this->once())
            ->method('updateMerchantOrder')
            ->with($deliveryOrderRequest)
            ->willReturn($merchantOrder);

        $result = $this->deliveryFacade->updateMerchantOrder($deliveryOrderRequest);

        $this->assertSame($merchantOrder, $result);
    }

    public function testCreateDeliveryOrderThrowsException(): void
    {
        $this->expectException(DeliveryOrderRequestException::class);

        $deliveryOrderRequest = $this->createMock(PayseraDeliveryOrderRequest::class);

        $this->deliveryOrderService
            ->expects($this->once())
            ->method('createDeliveryOrder')
            ->with($deliveryOrderRequest)
            ->willThrowException(new DeliveryOrderRequestException());

        $this->deliveryFacade->createDeliveryOrder($deliveryOrderRequest);
    }

    public function testUpdateMerchantOrderThrowsException(): void
    {
        $this->expectException(DeliveryGatewayNotFoundException::class);

        $deliveryOrderRequest = $this->createMock(PayseraDeliveryOrderRequest::class);

        $this->deliveryOrderCallbackService
            ->expects($this->once())
            ->method('updateMerchantOrder')
            ->with($deliveryOrderRequest)
            ->willThrowException(new DeliveryGatewayNotFoundException('gatewayCode', 'orderNumber'));

        $this->deliveryFacade->updateMerchantOrder($deliveryOrderRequest);
    }

    public function testValidateCredentialsReturnsTrue(): void
    {
        $credentials = new ProjectCredentials([
            'project_id' => '123456',
            'password' => '6943a905f5a1a5ebd29b4f3c4c15b818',
        ]);

        $this->deliveryOrderService
            ->expects($this->once())
            ->method('validateCredentials')
            ->with($this->callback(function ($arg) use ($credentials) {
                return $arg instanceof ProjectCredentials
                    && $arg->getProjectId() === $credentials->getProjectId()
                    && $arg->getPassword() === $credentials->getPassword();
            }))
            ->willReturn(true);

        $result = $this->deliveryFacade->validateCredentials($credentials);

        $this->assertTrue($result);
    }

    public function testValidateCredentialsReturnsFalse(): void
    {
        $credentials = new ProjectCredentials([
            'project_id' => '123456',
            'password' => 'invalid_password',
        ]);

        $this->deliveryOrderService
            ->expects($this->once())
            ->method('validateCredentials')
            ->with($this->callback(function ($arg) use ($credentials) {
                return $arg instanceof ProjectCredentials
                    && $arg->getProjectId() === $credentials->getProjectId()
                    && $arg->getPassword() === $credentials->getPassword();
            }))
            ->willReturn(false);

        $result = $this->deliveryFacade->validateCredentials($credentials);

        $this->assertFalse($result);
    }

    public function testValidateCredentialsThrowsException(): void
    {
        $this->expectException(CredentialsValidationException::class);

        $credentials = new ProjectCredentials([
            'project_id' => '123456',
            'password' => '6943a905f5a1a5ebd29b4f3c4c15b818',
        ]);

        $this->deliveryOrderService
            ->expects($this->once())
            ->method('validateCredentials')
            ->with($this->callback(function ($arg) use ($credentials) {
                return $arg instanceof ProjectCredentials
                    && $arg->getProjectId() === $credentials->getProjectId()
                    && $arg->getPassword() === $credentials->getPassword();
            }))
            ->willThrowException(new CredentialsValidationException('401 Unauthorized', null));

        $this->deliveryFacade->validateCredentials($credentials);
    }
}
