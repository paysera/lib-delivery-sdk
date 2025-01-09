<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Service;

use Paysera\DeliverySdk\Entity\DeliveryTerminalLocationInterface;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryGatewayInterface;

interface MerchantOrderLoggerInterface
{
    /**
     * @param MerchantOrderInterface $merchantOrder
     * @param array<string, mixed> $oldData
     * @param array<string, mixed> $newData
     * @return void
     */
    public function logShippingChanges(MerchantOrderInterface $merchantOrder, array $oldData, array $newData): void;

    public function logDeliveryGatewayChanges(
        MerchantOrderInterface $merchantOrder,
        PayseraDeliveryGatewayInterface $oldGateway,
        PayseraDeliveryGatewayInterface $newGateway
    ): void;

    public function logDeliveryTerminalLocationChanges(
        MerchantOrderInterface $merchantOrder,
        ?DeliveryTerminalLocationInterface $oldTerminalLocation,
        DeliveryTerminalLocationInterface $newTerminalLocation
    ): void;
}
