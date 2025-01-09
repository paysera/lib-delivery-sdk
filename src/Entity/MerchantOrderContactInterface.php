<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

interface MerchantOrderContactInterface
{
    public function getFirstName(): string;

    public function getLastName(): string;

    public function getCompany(): ?string;

    public function setPhone(?string $phone): self;

    public function getPhone(): string;

    public function setEmail(?string $email): self;

    public function getEmail(): string;
}
