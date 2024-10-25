<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Repository;

use Paysera\DeliveryApi\MerchantClient\Entity\Order;
use Paysera\DeliverySdk\Entity\PayseraDeliveryGatewayInterface;

interface DeliveryGatewayRepositoryInterface
{
    public function findPayseraGatewayForDeliveryOrder(Order $deliveryOrder): ?PayseraDeliveryGatewayInterface;
}
