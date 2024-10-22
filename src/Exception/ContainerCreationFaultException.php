<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Exception;

use Psr\Container\ContainerExceptionInterface;

class ContainerCreationFaultException extends BaseException implements ContainerExceptionInterface
{
    public function __construct(string $message)
    {
        parent::__construct($message, static::E_CONTAINER_CREATION_FAULT);
    }
}
