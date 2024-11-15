<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Service;

use ArrayAccess;
use Paysera\DeliverySdk\Dto\ObjectStateDto;

class ObjectStateService
{
    /**
     * @param ArrayAccess<string, mixed> $sourceObject
     * @param array<string> $fieldsPaths
     * @return ObjectStateDto
     */
    public function getState(ArrayAccess $sourceObject, array $fieldsPaths): ObjectStateDto
    {
        $data = [];

        foreach ($fieldsPaths as $fieldPath) {
            $data[$fieldPath] = $this->getFieldData($fieldPath, $sourceObject);
        }

        return new ObjectStateDto($data);
    }

    /**
     * @param ObjectStateDto $stateDto
     * @param ArrayAccess<string, mixed> $targetObject
     * @return void
     */
    public function setState(ObjectStateDto $stateDto, ArrayAccess $targetObject): void
    {
        foreach ($stateDto->getState() as $fieldPath => $value) {
            $this->setFieldData($fieldPath, $targetObject, $value);
        }
    }

    /**
     * @param ObjectStateDto $stateDto
     * @param array<string, string> $keyMap
     * @return ObjectStateDto
     */
    public function transformState(ObjectStateDto $stateDto, array $keyMap): ObjectStateDto
    {
        $newState = [];

        foreach ($stateDto->getState() as $fieldPath => $value) {
            if (isset($keyMap[$fieldPath])) {
                $newState[$keyMap[$fieldPath]] = $value;
            }
        }

        return new ObjectStateDto($newState);
    }

    /**
     * @param ObjectStateDto $left
     * @param ObjectStateDto $right
     * @param array<string, string>|null $keyMap
     * @return ObjectStateDto
     */
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
     * @param ArrayAccess<string, mixed> $object
     * @return mixed|null
     */
    private function getFieldData(string $path, ArrayAccess $object)
    {
        $path = explode('.', $path);
        $lastItemKey = array_key_last($path);
        $currentLevel = $object;

        foreach ($path as $key => $item) {
            if ($currentLevel instanceof ArrayAccess) {
                $data = $currentLevel->offsetGet($item) ?? null;
            } elseif (is_array($currentLevel)) {
                $data = $currentLevel[$item] ?? null;
            } else {
                continue;
            }

            if ($key === $lastItemKey) {
                return $data;
            }

            $currentLevel = $data;
        }

        return null;
    }

    /**
     * @param string $path
     * @param ArrayAccess<string, mixed> $object
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
