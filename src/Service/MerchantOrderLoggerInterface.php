<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Service;

use Paysera\DeliverySdk\Entity\DeliveryTerminalLocationInterface;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;

interface MerchantOrderLoggerInterface
{
    public function logShippingChanges(MerchantOrderInterface $merchantOrder, array $oldData, array $newData): void;
    public function logDeliveryTerminalLocationChanges(
        MerchantOrderInterface $merchantOrder,
        DeliveryTerminalLocationInterface $oldTerminalLocation,
        DeliveryTerminalLocationInterface $newTerminalLocation
    ): void;
}
