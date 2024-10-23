<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\Service;

use ArrayAccess;
use Paysera\DeliverySdk\Dto\ObjectStateDto;
use Paysera\DeliverySdk\Service\ObjectStateService;
use PHPUnit\Framework\TestCase;

class ObjectStateServiceTest extends TestCase
{
    private ObjectStateService $service;
    private ArrayAccess $sourceObject;
    private ArrayAccess $targetObject;

    protected function setUp(): void
    {
        $this->service = new ObjectStateService();
        $this->sourceObject = $this->createMock(ArrayAccess::class);
        $this->targetObject = $this->createMock(ArrayAccess::class);
    }

    public function testGetState(): void
    {
        $fieldsPaths = ['field1', 'field2.nested'];
        $expectedState = ['field1' => 'value1', 'field2.nested' => 'value2'];

        $subObject = $this->createMock(ArrayAccess::class);
        $subObject
            ->method('offsetGet')
            ->willReturnOnConsecutiveCalls('value2');

        $this->sourceObject
            ->method('offsetGet')
            ->willReturnCallback(
                function (string $key) use ($subObject) {
                    switch ($key) {
                        case 'field1':
                            return 'value1';
                        case 'field2':
                            return $subObject;
                    }
                }
            );

        $stateDto = $this->service->getState($this->sourceObject, $fieldsPaths);

        $this->assertInstanceOf(ObjectStateDto::class, $stateDto);
        $this->assertEquals($expectedState, $stateDto->getState());
    }

    public function testSetState(): void
    {
        $stateDto = new ObjectStateDto(['field1' => 'value1', 'field2.nested' => 'value2']);

        $subObject = $this->createMock(ArrayAccess::class);
        $subObject
            ->expects($this->exactly(1))
            ->method('offsetSet')
            ->willReturnCallback(
                function ($key, $value) {
                    if ($key == 'nested') {
                        $this->assertEquals('value2', $value);
                    }
                }
            );

        $this->targetObject
            ->expects($this->exactly(1))
            ->method('offsetGet')
            ->willReturnCallback(
                function ($key) use ($subObject) {
                    if ($key == 'field2') {
                        return $subObject;
                    }
                }
            );

        $this->targetObject
            ->expects($this->exactly(1))
            ->method('offsetSet')
            ->willReturnCallback(
                function ($key, $value) {
                    if ($key == 'level1') {
                        $this->assertEquals('value1', $value);
                    }
                }
            );

        $this->service->setState($stateDto, $this->targetObject);
    }

    public function testDiffState(): void
    {
        $leftState = new ObjectStateDto(['level1' => 'value1', 'level2' => 'value2']);
        $rightState = new ObjectStateDto(['level1' => 'value1', 'level2' => 'value3']);

        $result = $this->service->diffState($leftState, $rightState);

        $this->assertInstanceOf(ObjectStateDto::class, $result);
        $this->assertEquals(['level2' => 'value2'], $result->getState());
    }
}
