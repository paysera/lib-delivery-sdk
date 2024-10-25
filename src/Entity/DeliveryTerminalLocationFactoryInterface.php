<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

interface DeliveryTerminalLocationFactoryInterface
{
    public function create(): DeliveryTerminalLocationInterface;
}