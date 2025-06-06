<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\phpunit\Adapter;

use ArrayIterator;
use Paysera\DeliverySdk\Adapter\ShipmentsAdapter;
use Paysera\DeliverySdk\Collection\OrderItemsCollection;
use Paysera\DeliverySdk\Entity\MerchantOrderItemInterface;
use PHPUnit\Framework\TestCase;

class ShipmentsAdapterTest extends TestCase
{
    private ShipmentsAdapter $shipmentsAdapter;
    private MerchantOrderItemInterface $itemMock1;
    private MerchantOrderItemInterface $itemMock2;
    private OrderItemsCollection $itemsMockCollection;

    protected function setUp(): void
    {
        $this->shipmentsAdapter = new ShipmentsAdapter();
        $this->itemMock1 = $this->createMock(MerchantOrderItemInterface::class);
        $this->itemMock2 = $this->createMock(MerchantOrderItemInterface::class);
        $this->itemsMockCollection = new OrderItemsCollection([$this->itemMock1, $this->itemMock2]);
    }

    public function testConvert(): void
    {
        $mockData = [
            [
                'mock' => $this->itemMock1,
                'data' => [
                    'getHeight' => 10,
                    'getWidth' => 20,
                    'getLength' => 30,
                    'getWeight' => 40,
                ],
            ],
            [
                'mock' => $this->itemMock2,
                'data' => [
                    'getHeight' => 50,
                    'getWidth' => 60,
                    'getLength' => 70,
                    'getWeight' => 80,
                ],
            ],
        ];

        foreach ($mockData as $mockEntry) {
            $mock = $mockEntry['mock'];
            foreach ($mockEntry['data'] as $method => $value) {
                $mock->method($method)->willReturn($value);
            }
        }

        $shipments = [...$this->shipmentsAdapter->convert($this->itemsMockCollection)];

        $this->assertCount(1, $shipments);
        $shipment = $shipments[0];

        $this->assertEquals(70, $shipment->getLength());
        $this->assertEquals(60, $shipment->getWidth());

        $this->assertEquals(60, $shipment->getHeight());
        $this->assertEquals(120, $shipment->getWeight());
    }

    public function testConvertWithFiveItems(): void
    {
        $items = [];
        $totalHeight = 0.0;
        $totalWeight = 0.0;
        $maxLength = 0.0;
        $maxWidth = 0.0;

        for ($i = 1; $i <= 5; $i++) {
            $height = 5 * $i;
            $width = 10 * $i;
            $length = 15 * $i;
            $weight = 2 * $i;

            $mock = $this->createMock(MerchantOrderItemInterface::class);
            $mock->method('getHeight')->willReturn($height);
            $mock->method('getWidth')->willReturn($width);
            $mock->method('getLength')->willReturn($length);
            $mock->method('getWeight')->willReturn($weight);

            $items[] = $mock;

            $totalHeight += $height;
            $totalWeight += $weight;
            $maxLength = max($maxLength, $length);
            $maxWidth = max($maxWidth, $width);
        }

        $collection = new OrderItemsCollection($items);
        $shipments = [...$this->shipmentsAdapter->convert($collection)];

        $this->assertCount(1, $shipments);
        $shipment = $shipments[0];

        $this->assertSame($maxLength, $shipment->getLength());
        $this->assertSame($maxWidth, $shipment->getWidth());
        $this->assertSame($totalHeight, $shipment->getHeight());
        $this->assertSame($totalWeight, $shipment->getWeight());
    }
}
