<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Exception;

class InvalidTypeException extends BaseException
{
    public function __construct(string $requiredType)
    {
        parent::__construct("Value must be of type `$requiredType`.", self::E_INVALID_TYPE);
    }
}
