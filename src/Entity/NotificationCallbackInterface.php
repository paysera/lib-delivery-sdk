<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

interface NotificationCallbackInterface
{
    public function getUrl(): string;

    /**
     * @return array<string>
     */
    public function getEvents(): array;
}
