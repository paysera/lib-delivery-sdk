<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Adapter;

use Paysera\DeliveryApi\MerchantClient\Entity\Address;
use Paysera\DeliverySdk\Entity\MerchantOrderAddressInterface;

class AddressAdapter
{
    public function convert(MerchantOrderAddressInterface $address): Address
    {
        $addressDto = (new Address())
            ->setCountry($address->getCountry())
            ->setState($address->getState())
            ->setCity($address->getCity())
            ->setStreet($address->getStreet())
            ->setPostalCode($address->getPostalCode())
        ;

        if ($address->getHouseNumber() !== null) {
            $addressDto->setHouseNumber($address->getHouseNumber());
        }

        return $addressDto;
    }
}
