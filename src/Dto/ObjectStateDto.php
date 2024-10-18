<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Dto;

class ObjectStateDto
{
    private array $state;

    public function __construct(array $state)
    {
        $this->state = $state;
    }

    public function getState(): array
    {
        return $this->state;
    }
}
