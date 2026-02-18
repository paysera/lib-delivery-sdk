<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\Service;

use Paysera\DeliveryApi\MerchantClient\Entity\Order;
use Paysera\DeliveryApi\MerchantClient\Entity\ProjectCredentials;
use Paysera\DeliverySdk\Client\DeliveryApiClient;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Entity\PayseraDeliverySettingsInterface;
use Paysera\DeliverySdk\Exception\CredentialsValidationException;
use Paysera\DeliverySdk\Exception\MerchantClientNotFoundException;
use Paysera\DeliverySdk\Exception\RateLimitExceededException;
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

    public function testPrepaidDeliveryOrder(): void
    {
        $order = $this->createMock(MerchantOrderInterface::class);
        $deliveryOrderRequest = $this->createMock(PayseraDeliveryOrderRequest::class);

        $deliveryOrderRequest->method('getOrder')->willReturn($order);

        $deliveryOrder = $this->createMock(Order::class);

        $this->deliveryApiClient->expects($this->once())
            ->method('prepaidOrder')
            ->with($deliveryOrderRequest)
            ->willReturn($deliveryOrder);

        $this->logger->expects($this->exactly(2))
            ->method('info');

        $result = $this->service->prepaidDeliveryOrder($deliveryOrderRequest);

        $this->assertSame($order, $result);
    }

    public static function validateCredentialsResultProvider(): array
    {
        return [
            'valid credentials' => [
                'projectId' => '123456',
                'password' => '6943a905f5a1a5ebd29b4f3c4c15b818',
                'expectedResult' => true,
                'expectedResultString' => 'valid',
            ],
            'invalid credentials' => [
                'projectId' => '123456',
                'password' => 'invalid_password',
                'expectedResult' => false,
                'expectedResultString' => 'invalid',
            ],
        ];
    }

    /**
     * @dataProvider validateCredentialsResultProvider
     */
    public function testValidateCredentialsResult(
        string $projectId,
        string $password,
        bool $expectedResult,
        string $expectedResultString
    ): void {
        $credentials = new ProjectCredentials([
            'project_id' => $projectId,
            'password' => $password,
        ]);

        $this->deliveryApiClient->expects($this->once())
            ->method('validateCredentials')
            ->with($this->callback(function ($arg) use ($credentials) {
                return $arg instanceof ProjectCredentials
                    && $arg->getProjectId() === $credentials->getProjectId()
                    && $arg->getPassword() === $credentials->getPassword();
            }))
            ->willReturn($expectedResult);

        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                [$this->stringContains("Attempting to perform operation 'validate_credentials' for project id: {$projectId}")],
                [$this->stringContains("Operation 'validate_credentials' for project id {$projectId} completed with result: {$expectedResultString}")]
            );

        $result = $this->service->validateCredentials($credentials);

        $this->assertSame($expectedResult, $result);
    }

    public function testValidateCredentialsThrowsRateLimitExceededException(): void
    {
        $credentials = new ProjectCredentials([
            'project_id' => '123456',
            'password' => '6943a905f5a1a5ebd29b4f3c4c15b818',
        ]);

        $exception = new RateLimitExceededException(null);

        $this->deliveryApiClient->expects($this->once())
            ->method('validateCredentials')
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('info')
            ->with($this->stringContains("Attempting to perform operation 'validate_credentials' for project id: 123456"));

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains("Operation 'validate_credentials' for project id 123456 failed due to rate limit."),
                $exception
            );

        $this->expectException(RateLimitExceededException::class);

        $this->service->validateCredentials($credentials);
    }

    public function testValidateCredentialsThrowsMerchantClientNotFoundException(): void
    {
        $credentials = new ProjectCredentials([
            'project_id' => '123456',
            'password' => '6943a905f5a1a5ebd29b4f3c4c15b818',
        ]);

        $exception = new MerchantClientNotFoundException();

        $this->deliveryApiClient->expects($this->once())
            ->method('validateCredentials')
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('info')
            ->with($this->stringContains("Attempting to perform operation 'validate_credentials' for project id: 123456"));

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains("Operation 'validate_credentials' for project id 123456 failed: merchant client not found."),
                $exception
            );

        $this->expectException(MerchantClientNotFoundException::class);

        $this->service->validateCredentials($credentials);
    }

    public function testValidateCredentialsThrowsCredentialsValidationException(): void
    {
        $credentials = new ProjectCredentials([
            'project_id' => '123456',
            'password' => 'invalid_password',
        ]);

        $exception = new CredentialsValidationException('401 Unauthorized', null);

        $this->deliveryApiClient->expects($this->once())
            ->method('validateCredentials')
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('info')
            ->with($this->stringContains("Attempting to perform operation 'validate_credentials' for project id: 123456"));

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains("Operation 'validate_credentials' for project id 123456 failed."),
                $exception
            );

        $this->expectException(CredentialsValidationException::class);

        $this->service->validateCredentials($credentials);
    }
}
