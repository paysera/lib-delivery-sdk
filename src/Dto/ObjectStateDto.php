<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Dto;

class ObjectStateDto
{
    /**
     * @var array<string, mixed>
     */
    private array $state;

    /**
     * @param array<string, mixed> $state
     */
    public function __construct(array $state)
    {
        $this->state = $state;
    }

    /**
     * @return array<string, mixed>
     */
    public function getState(): array
    {
        return $this->state;
    }
}
