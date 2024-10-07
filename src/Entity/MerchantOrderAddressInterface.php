<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

interface MerchantOrderAddressInterface
{
    public function setCountry(string $country): void;

    public function getCountry(): string;

    public function setState(string $state): void;

    public function getState(): string;

    public function setCity(string $city): void;

    public function getCity(): string;

    public function setStreet(string $street): void;

    public function getStreet(): string;

    public function setPostalCode(string $postalCode): void;

    public function getPostalCode(): string;

    public function setHouseNumber(?string $houseNumber): void;

    public function getHouseNumber(): ?string;
}
