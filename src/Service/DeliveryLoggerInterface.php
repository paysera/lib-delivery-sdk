<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Service;

use Throwable;

interface DeliveryLoggerInterface
{
    public function info(string $message): void;

    public function error(string $message, Throwable $exception = null): void;
}
