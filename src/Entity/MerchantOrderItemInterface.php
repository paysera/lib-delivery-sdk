<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

interface MerchantOrderItemInterface
{
    public function getWeight(): int;

    public function getLength(): int;

    public function getWidth(): int;

    public function getHeight(): int;
}
