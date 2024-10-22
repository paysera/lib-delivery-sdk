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

    protected function setUp(): void
    {
        $this->orderMock = $this->createMock(MerchantOrderInterface::class);
        $this->deliverySettingsMock = $this->createMock(PayseraDeliverySettingsInterface::class);
    }

    public function testConstructorAndGetters(): void
    {
        $payseraDeliveryOrderRequest = new PayseraDeliveryOrderRequest(
            $this->orderMock,
            $this->deliverySettingsMock,
        );

        $this->assertSame($this->orderMock, $payseraDeliveryOrderRequest->getOrder());
        $this->assertSame($this->deliverySettingsMock, $payseraDeliveryOrderRequest->getDeliverySettings());
    }
}
