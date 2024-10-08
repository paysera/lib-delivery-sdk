<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

use ArrayAccess;

interface MerchantOrderAddressInterface extends ArrayAccess
{
    public function setCountry(string $country): self;

    public function getCountry(): string;

    public function setState(string $state): self;

    public function getState(): string;

    public function setCity(string $city): self;

    public function getCity(): string;

    public function setStreet(string $street): self;

    public function getStreet(): string;

    public function setPostalCode(string $postalCode): self;

    public function getPostalCode(): string;

    public function setHouseNumber(?string $houseNumber): self;

    public function getHouseNumber(): ?string;
}
