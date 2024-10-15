<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Exception;

use Exception;
use Throwable;

class DeliveryOrderRequestException extends Exception
{
    protected $message = 'Delivery order request failed';

    public function __construct(Throwable $previous = null)
    {
        parent::__construct($this->message, 0, $previous);
    }
}
