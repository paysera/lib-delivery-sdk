<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

interface DeliveryTerminalLocationInterface
{
    public function getCountryCode(): string;

    public function getCity(): string;

    public function getSelectedTerminalId(): string;

    public function getDeliveryGatewayCode(): string;
}
