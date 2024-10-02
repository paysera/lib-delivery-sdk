<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

interface MerchantOrderAddressInterface
{
    public function getCountry(): string;

    public function getState(): string;

    public function getCity(): string;

    public function getStreet(): string;

    public function getPostalCode(): string;

    public function getHouseNumber(): ?string;
}
