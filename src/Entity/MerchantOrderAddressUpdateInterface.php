<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

interface MerchantOrderAddressUpdateInterface extends MerchantOrderAddressInterface
{
    public function setCountry(string $country): void;

    public function setState(string $state): void;

    public function setCity(string $city): void;

    public function setStreet(string $street): void;

    public function setPostalCode(string $postalCode): void;

    public function setHouseNumber(?string $houseNumber): void;
}