<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

interface MerchantOrderPartyUpdateInterface extends MerchantOrderPartyInterface
{
    public function getContact(): MerchantOrderContactUpdateInterface;

    public function getAddress(): MerchantOrderAddressUpdateInterface;

    public function getTerminalLocationDto(): ?DeliveryTerminalLocationUpdateInterface;
}
