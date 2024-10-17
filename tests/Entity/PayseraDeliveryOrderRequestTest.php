<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\phpunit\Entity;

use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryGatewaySettingsInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Entity\PayseraDeliverySettingsInterface;
use PHPUnit\Framework\TestCase;

class PayseraDeliveryOrderRequestTest extends TestCase
{
    private MerchantOrderInterface $orderMock;
    private PayseraDeliverySettingsInterface $deliverySettingsMock;
    private PayseraDeliveryGatewaySettingsInterface $deliveryGatewaySettingsMock;
    private string $deliveryGatewayCode = 'DHL';
    private int $deliveryGatewayInstanceId = 123;

    protected function setUp(): void
    {
        $this->orderMock = $this->createMock(MerchantOrderInterface::class);
        $this->deliverySettingsMock = $this->createMock(PayseraDeliverySettingsInterface::class);
        $this->deliveryGatewaySettingsMock = $this->createMock(PayseraDeliveryGatewaySettingsInterface::class);
    }

    public function testConstructorAndGetters(): void
    {
        $payseraDeliveryOrderRequest = new PayseraDeliveryOrderRequest(
            $this->orderMock,
            $this->deliverySettingsMock,
            $this->deliveryGatewaySettingsMock,
            $this->deliveryGatewayCode,
            $this->deliveryGatewayInstanceId
        );

        $this->assertSame($this->orderMock, $payseraDeliveryOrderRequest->getOrder());
        $this->assertSame($this->deliverySettingsMock, $payseraDeliveryOrderRequest->getDeliverySettings());
        $this->assertSame(
            $this->deliveryGatewaySettingsMock,
            $payseraDeliveryOrderRequest->getDeliveryGatewaySettings()
        );
        $this->assertSame($this->deliveryGatewayCode, $payseraDeliveryOrderRequest->getDeliveryGatewayCode());
        $this->assertSame(
            $this->deliveryGatewayInstanceId,
            $payseraDeliveryOrderRequest->getDeliveryGatewayInstanceId()
        );
    }
}
