<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Collection;

use ArrayAccess;
use Iterator;
use Paysera\DeliverySdk\Entity\MerchantOrderItemInterface;

class OrderItemsCollection extends Collection
{
    public function isCompatible(object $item): bool
    {
        return $item instanceof MerchantOrderItemInterface;
    }

    public function current(): MerchantOrderItemInterface
    {
        return parent::current();
    }

    public function getItemType(): string
    {
        return MerchantOrderItemInterface::class;
    }
}
