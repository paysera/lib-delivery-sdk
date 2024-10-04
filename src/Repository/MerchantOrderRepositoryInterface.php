<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Repository;

use Paysera\DeliverySdk\Entity\MerchantOrderInterface;

interface MerchantOrderRepositoryInterface
{
    public function findByNumber(string $number): MerchantOrderInterface;

    public function save(MerchantOrderInterface $order): void;
}