<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

interface MerchantOrderContactUpdateInterface extends MerchantOrderContactInterface
{
    public function setEmail(string $email): void;
}