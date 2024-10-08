<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

use ArrayAccess;

interface DeliveryTerminalLocationInterface extends ArrayAccess
{
    public function setCountryCode(string $countryCode): void;

    public function getCountryCode(): string;

    public function setCity(string $city): void;

    public function setTerminalId(string $terminalId): void;

    public function getCity(): string;

    public function getTerminalId(): string;

    public function setDeliveryGatewayCode(string $gatewayCode): void;

    public function getDeliveryGatewayCode(): string;
}
