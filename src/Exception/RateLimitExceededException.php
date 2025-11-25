<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Exception;

use Throwable;

class RateLimitExceededException extends BaseException
{
    private const MESSAGE = 'Rate limit exceeded. Too many validation attempts.';
    public function __construct(
        Throwable $previous = null,
        int $code = self::E_RATE_LIMIT_EXCEEDED
    ) {
        parent::__construct(self::MESSAGE, $code, $previous);
    }
}
