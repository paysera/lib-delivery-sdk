<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Adapter;

use Paysera\DeliveryApi\MerchantClient\Entity\OrderNotificationCreate;
use Paysera\Dto\EshopOrderNotificationCallbackDto;
use Paysera\DeliverySdk\Entity\NotificationCallbackInterface;

class OrderNotificationAdapter
{
    public function convert(NotificationCallbackInterface $callbackDto): OrderNotificationCreate
    {
        return (new OrderNotificationCreate())
            ->setUrl($callbackDto->getUrl())
            ->setEvents($callbackDto->getEvents())
        ;
    }
}