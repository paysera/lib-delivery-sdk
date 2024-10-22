<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Service;

use ArrayAccess;
use Paysera\DeliverySdk\Dto\ObjectStateDto;

class ObjectStateService
{
    public function getState(ArrayAccess $sourceObject, array $fieldsPaths): ObjectStateDto
    {
        $data = [];

        foreach ($fieldsPaths as $fieldPath) {
            $data[$fieldPath] = $this->getFieldData($fieldPath, $sourceObject);
        }

        return new ObjectStateDto($data);
    }

    public function setState(ObjectStateDto $stateDto, ArrayAccess $targetObject): void
    {
        foreach ($stateDto->getState() as $fieldPath => $value) {
            $this->setFieldData($fieldPath, $targetObject, $value);
        }
    }

    public function diffState(ObjectStateDto $left, ObjectStateDto $right, ?array $keyMap = null): ObjectStateDto
    {
        $leftData = $left->getState();
        $rightData = $right->getState();

        if ($keyMap !== null) {
            $rightData = array_map(
                fn (string $rightKey) => $rightData[$rightKey],
                $keyMap
            );
        }

        return new ObjectStateDto(
            array_diff($leftData, $rightData)
        );
    }

    /**
     * @param string $path
     * @param ArrayAccess $object
     * @return mixed|null
     */
    private function getFieldData(string $path, ArrayAccess $object)
    {
        $path = explode('.', $path);
        $lastItemKey = array_key_last($path);
        $currentLevel = $object;

        foreach ($path as $key => $item) {
            $data = $currentLevel->offsetGet($item) ?? null;

            if ($key === $lastItemKey) {
                return $data;
            }

            $currentLevel = $data;
        }

        return null;
    }

    /**
     * @param string $path
     * @param ArrayAccess $object
     * @param mixed $data
     * @return void
     */
    private function setFieldData(string $path, ArrayAccess $object, $data): void
    {
        $path = explode('.', $path);
        $lastItemKey = array_key_last($path);
        $currentLevel = $object;

        foreach ($path as $key => $item) {
            if ($currentLevel === null) {
                return;
            }

            if ($key === $lastItemKey) {
                $currentLevel->offsetSet($item, $data);

                return;
            }

            $currentLevel = $currentLevel->offsetGet($item) ?? null;
        }
    }
}
