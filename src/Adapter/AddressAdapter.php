<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Adapter;

use Paysera\DeliveryApi\MerchantClient\Entity\Address;
use Paysera\DeliverySdk\Entity\MerchantOrderAddressInterface;

class AddressAdapter
{
    public function convert(MerchantOrderAddressInterface $addressDto): Address
    {
        $addressDto = (new Address())
            ->setCountry($addressDto->getCountry())
            ->setState($addressDto->getState())
            ->setCity($addressDto->getCity())
            ->setStreet($addressDto->getStreet())
            ->setPostalCode($addressDto->getPostalCode())
        ;

        if ($addressDto->getHouseNumber() !== null) {
            $addressDto->setHouseNumber($addressDto->getHouseNumber());
        }

        return $addressDto;
    }
}
