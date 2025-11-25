<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Exception;

use Throwable;

class CredentialsValidationException extends BaseException
{
    public function __construct(string $message, Throwable $previous = null)
    {
        parent::__construct(
            $message,
            self::E_CREDENTIALS_VALIDATION_FAILED,
            $previous
        );
    }
}
