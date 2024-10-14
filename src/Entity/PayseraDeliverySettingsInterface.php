<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

interface PayseraDeliverySettingsInterface
{
    public function getProjectId(): ?int;

    public function getResolvedProjectId(): ?string;

    public function getProjectPassword(): ?string;

    public function isTestModeEnabled(): ?bool;

    public function isHouseNumberFieldEnabled(): ?bool;
}
