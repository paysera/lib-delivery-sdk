<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

interface PayseraDeliveryGatewaySettingsInterface
{
    public function getMinimumWeight(): float;

    public function setMinimumWeight(float $minimumWeight): self;

    public function getMaximumWeight(): float;

    public function setMaximumWeight(float $maximumWeight): self;

    public function getSenderType(): ?string;

    public function setSenderType(string $senderType): self;

    public function getReceiverType(): ?string;

    public function setReceiverType(string $receiverType): self;
}
