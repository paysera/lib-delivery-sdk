<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Repository;

use Paysera\DeliverySdk\Entity\PayseraDeliveryGatewayInterface;

interface DeliveryGatewayRepositoryInterface
{
    public function findPayseraGateway(string $gatewayCode): ?PayseraDeliveryGatewayInterface;
}
