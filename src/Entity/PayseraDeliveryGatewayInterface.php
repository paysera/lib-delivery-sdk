<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

interface PayseraDeliveryGatewayInterface
{
    public function getCode(): string;

    public function getName(): string;

    public function getFee(): float;

    public function getSettings(): PayseraDeliveryGatewaySettingsInterface;
}
