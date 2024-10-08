<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Service;

use Paysera\DeliverySdk\Entity\MerchantOrderInterface;

interface MerchantOrderLoggerInterface
{
    public function logShippingChanges(MerchantOrderInterface $merchantOrder, array $oldData, array $newData): void;
}
