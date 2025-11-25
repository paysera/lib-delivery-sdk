<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\Client;

use Paysera\DeliveryApi\MerchantClient\Entity\Order;
use Paysera\DeliveryApi\MerchantClient\Entity\OrderCreate;
use Paysera\DeliveryApi\MerchantClient\Entity\OrderUpdate;
use Paysera\DeliveryApi\MerchantClient\Entity\ProjectCredentials;
use Paysera\DeliveryApi\MerchantClient\MerchantClient;
use Paysera\DeliverySdk\Adapter\DeliveryOrderRequestAdapterFacade;
use Paysera\DeliverySdk\Client\DeliveryOrderApiClient;
use Paysera\DeliverySdk\Client\Provider\MerchantClientProvider;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Entity\PayseraDeliverySettingsInterface;
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

    public static function validateCredentialsResultProvider(): array
    {
        return [
            'valid credentials' => [
                'projectId' => '123456',
                'password' => '6943a905f5a1a5ebd29b4f3c4c15b818',
                'expectedResult' => true,
            ],
            'invalid credentials' => [
                'projectId' => '123456',
                'password' => 'invalid_password',
                'expectedResult' => false,
            ],
        ];
    }

    /**
     * @dataProvider validateCredentialsResultProvider
     */
    public function testValidateCredentialsResult(
        string $projectId,
        string $password,
        bool $expectedResult
    ): void {
        $credentials = new ProjectCredentials([
            'project_id' => $projectId,
            'password' => $password,
        ]);

        $this->merchantClientProviderMock
            ->expects($this->once())
            ->method('getPublicMerchantClient')
            ->willReturn($this->merchantClientMock);

        $this->merchantClientMock
            ->expects($this->once())
            ->method('validateProjectCredentials')
            ->with($this->callback(function ($arg) use ($credentials) {
                return $arg instanceof ProjectCredentials
                    && $arg->getProjectId() === $credentials->getProjectId()
                    && $arg->getPassword() === $credentials->getPassword();
            }))
            ->willReturn($expectedResult);

        $result = $this->deliveryOrderApiClient->validateCredentials($credentials);

        $this->assertSame($expectedResult, $result);
    }

    public function testValidateCredentialsThrowsRuntimeException(): void
    {
        $credentials = new ProjectCredentials([
            'project_id' => '123456',
            'password' => '6943a905f5a1a5ebd29b4f3c4c15b818',
        ]);

        $this->merchantClientProviderMock
            ->expects($this->once())
            ->method('getPublicMerchantClient')
            ->willReturn($this->merchantClientMock);

        $this->merchantClientMock
            ->expects($this->once())
            ->method('validateProjectCredentials')
            ->with($this->callback(function ($arg) use ($credentials) {
                return $arg instanceof ProjectCredentials
                    && $arg->getProjectId() === $credentials->getProjectId()
                    && $arg->getPassword() === $credentials->getPassword();
            }))
            ->willThrowException(new \RuntimeException('Rate limit exceeded'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $this->deliveryOrderApiClient->validateCredentials($credentials);
    }
}
