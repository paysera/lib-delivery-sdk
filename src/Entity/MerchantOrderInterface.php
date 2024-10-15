<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

use Paysera\DeliverySdk\Collection\OrderItemsCollection;

interface MerchantOrderInterface
{
    public function getNumber(): string;

    public function getDeliverOrderId(): string;

    public function getDeliverOrderNumber(): string;

    public function setDeliverOrderId(string $id): self;

    public function setDeliverOrderNumber(string $number): self;

    public function getShipping(): MerchantOrderPartyInterface;

    public function getBilling(): ?MerchantOrderPartyInterface;

    public function getItems(): OrderItemsCollection;

    public function getNotificationCallback(): ?NotificationCallbackInterface;
}