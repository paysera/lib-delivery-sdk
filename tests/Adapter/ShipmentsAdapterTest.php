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

        foreach ($mockData as $index => $mockEntry) {
            $mock = $mockEntry['mock'];
            foreach ($mockEntry['data'] as $method => $value) {
                $mock->method($method)->willReturn($value);
            }
        }

        $shipments = [...$this->shipmentsAdapter->convert($this->itemsMockCollection)];

        $this->assertCount(2, $shipments);

        foreach ($shipments as $index => $shipment) {
            $mockDataEntry = $mockData[$index]['data'];
            foreach ($mockDataEntry as $method => $expectedValue) {
                $property = lcfirst(substr($method, 3));
                $this->assertSame($expectedValue, $shipment->{"get" . ucfirst($property)}());
            }
        }
    }
}
