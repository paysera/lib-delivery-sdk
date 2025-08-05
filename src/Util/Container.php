<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Util;

use Exception;
use Paysera\DeliverySdk\Exception\ContainerCreationFaultException;
use Paysera\DeliverySdk\Exception\ContainerNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

class Container implements ContainerInterface
{
    protected array $instances;

    public function __construct()
    {
        $this->instances = [];
    }

    public function has(string $id): bool
    {
        return isset($this->instances[$id]);
    }

    public function get(string $id): object
    {
        if ($this->has($id) === false) {
            try {
                $instance = $this->build($id);
            } catch (Exception $exception) {
                throw new ContainerNotFoundException("Service with id `$id` not found in container.");
            }

            $this->set($id, $instance);
        }

        return $this->instances[$id];
    }

    public function set(string $id, object $concrete = null): self
    {
        $this->instances[$id] = $concrete;

        return $this;
    }

    /**
     * @param string $id
     * @return object
     * @throws ContainerCreationFaultException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function build(string $id): object
    {
        try {
            $instance = $this->createInstance($id);
        } catch (ReflectionException $exception) {
            throw new ContainerCreationFaultException("Class $id has instantiable issues.");
        }

        return $instance;
    }

    /**
     * @param string $className
     * @return object
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    protected function createInstance(string $className): object
    {
        if (!class_exists($className)) {
            throw new ContainerCreationFaultException("Class $className does not exist");
        }
        
        $reflector = new ReflectionClass($className);

        if (!$reflector->isInstantiable()) {
            throw new ContainerCreationFaultException("Class $className is not instantiable");
        }

        $constructor = $reflector->getConstructor();
        if ($constructor === null) {
            return $reflector->newInstance();
        }

        $constructorParameters = $constructor->getParameters();
        $dependencies = $this->getDependencies($constructorParameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * @param ReflectionParameter[] $constructorParameters
     * @return array
     * @throws ContainerCreationFaultException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getDependencies(array $constructorParameters): array
    {
        $dependencies = [];

        foreach ($constructorParameters as $constructorParameter) {
            /** @var ReflectionNamedType|null $type */
            $type = $constructorParameter->getType();

            if ($type === null) {
                if ($constructorParameter->isDefaultValueAvailable()) {
                    $dependencies[] = $constructorParameter->getDefaultValue();
                } else {
                    throw new ContainerCreationFaultException("Can not resolve class dependency $constructorParameter->name");
                }
            } else {
                $dependencies[] = $this->get($type->getName());
            }
        }

        return $dependencies;
    }
}
