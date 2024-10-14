<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Collection;

use ArrayAccess;
use Iterator;
use Paysera\DeliverySdk\Entity\MerchantOrderItemInterface;

class OrderItemsCollection implements Iterator, ArrayAccess
{
    private array $items = [];

    private int $position = 0;

    public function offsetExists($offset): bool
    {
        return \array_key_exists($offset, $this->items);
    }

    public function offsetGet($offset): MerchantOrderItemInterface
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    public function get(int $key): ?MerchantOrderItemInterface
    {
        if (\array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }
        return null;
    }

    /**
     * @param int $key
     * @param MerchantOrderItemInterface $value
     * @return $this
     */
    public function set(int $key, MerchantOrderItemInterface $value): self
    {
        $this->items[$key] = $value;

        return $this;
    }

    public function current(): MerchantOrderItemInterface
    {
        return $this->items[$this->position];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }
}