<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\phpunit\Collection;

use Mockery as m;
use Paysera\DeliverySdk\Collection\OrderItemsCollection;
use Paysera\DeliverySdk\Entity\MerchantOrderItemInterface;
use Paysera\DeliverySdk\Exception\BaseException;
use Paysera\DeliverySdk\Exception\InvalidTypeException;
use PHPUnit\Framework\TestCase;
use stdClass;

class OrderItemsCollectionTest extends TestCase
{
    /**
     * @dataProvider isCompatibleDataProvider
     */
    public function testExchangeArray(string $class, bool $isCompatible): void
    {
        $collection = new OrderItemsCollection();

        if ($isCompatible === false) {
            $this->expectException(InvalidTypeException::class);
            $this->expectExceptionCode(BaseException::E_INVALID_TYPE);
        }

        $collection->exchangeArray([m::mock($class)]);

        $this->assertCount(
            (int) $isCompatible,
            $collection,
            'The collection must be not empty after the array exchange.'
        );
    }

    /**
     * @dataProvider isCompatibleDataProvider
     */
    public function testIsCompatible(string $class, bool $isCompatible, string $message): void
    {
        $collection = new OrderItemsCollection();

        $this->assertEquals($isCompatible, $collection->isCompatible(m::mock($class)), $message);
    }

    public function testAppend(): void
    {
        $collection = new OrderItemsCollection();
        $collection->append(m::mock(MerchantOrderItemInterface::class));

        $this->assertCount(
            1,
            $collection,
            'The collection must be not empty after the item append.'
        );
    }

    public function testAppendInvalidType(): void
    {
        $collection = new OrderItemsCollection();

        $this->expectException(InvalidTypeException::class);

        $collection->append(m::mock(stdClass::class));
    }

    public function testKey(): void
    {
        $collection = new OrderItemsCollection();

        $this->assertEquals(0, $collection->key(), 'The default collection key must return.');
    }

    public function testCurrent(): void
    {
        $collection = new OrderItemsCollection([m::mock(MerchantOrderItemInterface::class)]);

        foreach ($collection as $item) {
            $this->assertInstanceOf(
                MerchantOrderItemInterface::class,
                $item,
                'The collection item type is invalid.'
            );
        }
    }

    public function testFilter(): void
    {
        $orderItem1 = m::mock(MerchantOrderItemInterface::class);
        $orderItem1->shouldReceive('getWeight')->andReturn(50);
        $orderItem2 = m::mock(MerchantOrderItemInterface::class);
        $orderItem2->shouldReceive('getWeight')->andReturn(60);
        $collection = new OrderItemsCollection([$orderItem1, $orderItem2]);

        $filteredCollection = $collection->filter(
            static fn (MerchantOrderItemInterface $orderItem) => $orderItem->getWeight() === 50
        );

        $this->assertCount(
            1,
            $filteredCollection,
            'The filtered collection must not be empty.'
        );
        $this->assertEquals(
            50,
            $filteredCollection->get()->getWeight(),
            'The filtered collection item must correspond to the filter condition.'
        );
    }

    public function isCompatibleDataProvider(): array
    {
        return [
            'compatibleItem'   => [MerchantOrderItemInterface::class, true, 'The entity must be compatible.'],
            'incompatibleItem' => [stdClass::class, false, 'The entity must be not compatible.'],
        ];
    }
}
