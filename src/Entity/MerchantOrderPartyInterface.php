<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

interface MerchantOrderPartyInterface
{
    public function getContact(): MerchantOrderContactInterface;

    public function getAddress(): MerchantOrderAddressInterface;

    public function getTerminalLocationDto(): ?DeliveryTerminalLocationInterface;
}
