<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

use Paysera\DeliverySdk\Collection\OrderItemsCollection;

interface MerchantOrderInterface
{
    public function getNumber(): string;

    public function getDeliverOrderNumber(): string;

    public function getShipping(): MerchantOrderPartyInterface;

    public function getBilling(): ?MerchantOrderPartyInterface;

    public function getItems(): OrderItemsCollection;

    public function getNotificationCallback(): ?NotificationCallbackInterface;
}