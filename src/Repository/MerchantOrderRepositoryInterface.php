<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Repository;

use Paysera\DeliverySdk\Entity\MerchantOrderInterface;

interface MerchantOrderRepositoryInterface
{
    public function save(MerchantOrderInterface $order): void;
}
