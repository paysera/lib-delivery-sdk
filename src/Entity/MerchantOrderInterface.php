<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

use ArrayAccess;
use Paysera\DeliverySdk\Collection\OrderItemsCollection;

interface MerchantOrderInterface extends ArrayAccess
{
    public function getNumber(): string;

    public function getDeliveryOrderId(): string;

    public function getDeliveryOrderNumber(): string;

    public function setDeliveryOrderId(string $id): self;

    public function setDeliveryOrderNumber(string $number): self;

    public function getShipping(): MerchantOrderPartyInterface;

    public function getBilling(): ?MerchantOrderPartyInterface;

    /**
     * @return OrderItemsCollection<MerchantOrderItemInterface>
     */
    public function getItems(): OrderItemsCollection;

    public function getNotificationCallback(): ?NotificationCallbackInterface;

    public function getDeliveryGateway(): ?PayseraDeliveryGatewayInterface;

    public function setDeliveryGateway(PayseraDeliveryGatewayInterface $deliveryGateway): self;
}
