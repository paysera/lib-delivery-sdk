<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Exception;

use Exception;

abstract class BaseException extends Exception
{
    public const E_GATEWAY_NOT_FOUND = 1;
    public const E_FAILED_REQUEST = 2;
    public const E_MERCHANT_CLIENT_NOT_FOUND = 3;
    public const E_CONTAINER_CREATION_FAULT = 4;
    public const E_CONTAINER_NOT_FOUND = 5;
    public const E_INVALID_TYPE = 6;
    public const E_UNDEFINED_DELIVERY_GATEWAY = 7;
}
