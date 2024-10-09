<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

use ArrayAccess;

interface MerchantOrderPartyInterface extends ArrayAccess
{
    public function getContact(): MerchantOrderContactInterface;

    public function getAddress(): MerchantOrderAddressInterface;

    public function setTerminalLocation(?DeliveryTerminalLocationInterface $terminalLocation): self;

    public function getTerminalLocation(): ?DeliveryTerminalLocationInterface;
}
