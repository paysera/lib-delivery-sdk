<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Repository;

use Paysera\DeliverySdk\Entity\MerchantOrderPartyUpdateInterface;

interface MerchantOrderRepositoryInterface
{
    public function findByNumber(string $number): MerchantOrderPartyUpdateInterface;

    public function save(MerchantOrderPartyUpdateInterface $order): void;
}