<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

use Paysera\Collection\OrderItemsCollection;

interface MerchantOrderUpdateInterface extends MerchantOrderInterface
{
    public function getShipping(): MerchantOrderPartyUpdateInterface;

    public function getBilling(): ?MerchantOrderPartyUpdateInterface;
}