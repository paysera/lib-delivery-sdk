<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Utils;

use Paysera\DeliverySdk\Entity\PayseraDeliveryGatewaySettingsInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliverySettingsInterface;

class DeliveryGatewayUtils
{
    public static function resolveDeliveryGatewayCode(string $deliveryGatewayCode): string
    {
        $lastDelimPosition = strripos($deliveryGatewayCode, ':');

        return str_replace(
            [
                '_' . PayseraDeliverySettingsInterface::TYPE_COURIER,
                '_' . PayseraDeliverySettingsInterface::TYPE_PARCEL_MACHINE,
                '_' . PayseraDeliverySettingsInterface::TYPE_TERMINALS,
                PayseraDeliverySettingsInterface::DELIVERY_GATEWAY_PREFIX
            ],
            '',
            $lastDelimPosition
                ? substr($deliveryGatewayCode, 0, $lastDelimPosition)
                : $deliveryGatewayCode
        );
    }

    public static function getShipmentMethodCode(
        PayseraDeliveryGatewaySettingsInterface $deliveryGatewaySettings
    ): string {
        return sprintf(
            '%s2%s',
            $deliveryGatewaySettings->getSenderType(),
            $deliveryGatewaySettings->getReceiverType()
        );
    }
}