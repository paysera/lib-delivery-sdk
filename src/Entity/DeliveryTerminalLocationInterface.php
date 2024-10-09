<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

use ArrayAccess;

interface DeliveryTerminalLocationInterface extends ArrayAccess
{
    public function setCountry(string $country): self;

    public function getCountry(): string;

    public function setCity(string $city): self;

    public function getCity(): string;

    public function setTerminalId(string $terminalId): self;

    public function getTerminalId(): string;

    public function setDeliveryGatewayCode(string $gatewayCode): self;

    public function getDeliveryGatewayCode(): string;
}
