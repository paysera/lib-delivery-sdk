<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

interface MerchantOrderContactInterface
{
    public function setEmail(string $email): void;

    public function getFirstName(): string;

    public function getLastName(): string;

    public function getCompany(): ?string;

    public function getPhone(): string;

    public function getEmail(): string;
}
