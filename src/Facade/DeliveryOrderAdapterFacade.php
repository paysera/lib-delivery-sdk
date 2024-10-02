<?php

declare(strict_types=1);


use Paysera\DeliverySdk\Adapter\OrderCreateAdapter;
use Paysera\DeliverySdk\Adapter\OrderUpdateAdapter;
use Paysera\DeliveryApi\MerchantClient\Entity\OrderCreate;
use Paysera\DeliveryApi\MerchantClient\Entity\OrderUpdate;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;

class DeliveryOrderAdapterFacade
{
    private OrderCreateAdapter $createMapper;
    private OrderUpdateAdapter $updateMapper;

    public function __construct(
        OrderCreateAdapter $createMapper,
        OrderUpdateAdapter $updateMapper
    ) {

        $this->createMapper = $createMapper;
        $this->updateMapper = $updateMapper;
    }

    public function convertCreate(MerchantOrderInterface $dto): OrderCreate
    {
        return $this->createMapper->convert($dto);
    }

    public function convertUpdate(MerchantOrderInterface $dto): OrderUpdate
    {
        return $this->updateMapper->convert($dto);
    }
}
