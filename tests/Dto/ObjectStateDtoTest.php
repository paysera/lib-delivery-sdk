<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\phpunit\Dto;

use Paysera\DeliverySdk\Dto\ObjectStateDto;
use PHPUnit\Framework\TestCase;

class ObjectStateDtoTest extends TestCase
{
    private ObjectStateDto $objectStateDto;

    protected function setUp(): void
    {
        $this->objectStateDto = new ObjectStateDto(['key' => 'value']);
    }

    public function testGetStateReturnsCorrectState(): void
    {
        $expectedState = ['key' => 'value'];
        $this->assertSame($expectedState, $this->objectStateDto->getState());
    }

    public function testGetStateReturnsEmptyArrayWhenInitializedWithEmptyArray(): void
    {
        $objectStateDto = new ObjectStateDto([]);
        $this->assertSame([], $objectStateDto->getState());
    }

    public function testGetStateReturnsStateWithMultipleItems(): void
    {
        $state = ['key1' => 'value1', 'key2' => 'value2'];
        $objectStateDto = new ObjectStateDto($state);
        $this->assertSame($state, $objectStateDto->getState());
    }
}
