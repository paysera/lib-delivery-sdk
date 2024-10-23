<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

class PayseraDeliveryOrderRequest
{
    private MerchantOrderInterface $order;
    private PayseraDeliverySettingsInterface $deliverySettings;

    public function __construct(
        MerchantOrderInterface $order,
        PayseraDeliverySettingsInterface $deliverySettings
    ) {
        $this->order = $order;
        $this->deliverySettings = $deliverySettings;
    }

    public function getOrder(): MerchantOrderInterface
    {
        return $this->order;
    }

    public function getDeliverySettings(): PayseraDeliverySettingsInterface
    {
        return $this->deliverySettings;
    }
}
