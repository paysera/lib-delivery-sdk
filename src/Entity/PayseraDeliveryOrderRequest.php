<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

class PayseraDeliveryOrderRequest
{
    private MerchantOrderInterface $order;
    private PayseraDeliveryGatewaySettingsInterface $deliveryGatewaySettings;
    private string $deliveryGatewayCode;
    private int $deliveryGatewayInstanceId;
    private PayseraDeliverySettingsInterface $deliverySettings;

    public function __construct(
        MerchantOrderInterface $order,
        PayseraDeliverySettingsInterface $deliverySettings,
        PayseraDeliveryGatewaySettingsInterface $deliveryGatewaySettings,
        string $deliveryGatewayCode,
        int $deliveryGatewayInstanceId
    ) {
        $this->order = $order;
        $this->deliveryGatewaySettings = $deliveryGatewaySettings;
        $this->deliverySettings = $deliverySettings;
        $this->deliveryGatewayCode = $deliveryGatewayCode;
        $this->deliveryGatewayInstanceId = $deliveryGatewayInstanceId;
    }

    public function getOrder(): MerchantOrderInterface
    {
        return $this->order;
    }

    public function getDeliverySettings(): PayseraDeliverySettingsInterface
    {
        return $this->deliverySettings;
    }

    public function getDeliveryGatewaySettings(): PayseraDeliveryGatewaySettingsInterface
    {
        return $this->deliveryGatewaySettings;
    }

    public function getDeliveryGatewayCode(): string
    {
        return $this->deliveryGatewayCode;
    }

    public function getDeliveryGatewayInstanceId(): int
    {
        return $this->deliveryGatewayInstanceId;
    }
}
