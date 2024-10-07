<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

interface DeliveryTerminalLocationUpdateInterface extends DeliveryTerminalLocationInterface
{
    public function setCountryCode(string $countryCode): void;

    public function setCity(string $city): void;

    public function setSelectedTerminalId(string $terminalId): void;

    public function setDeliveryGatewayCode(string $gatewayCode): void;
}
