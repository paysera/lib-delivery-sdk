<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Exception;

use Throwable;

class DeliveryOrderRequestException extends BaseException
{
    protected $message = 'Delivery order request failed';

    public function __construct(Throwable $previous = null)
    {
        parent::__construct($this->message, self::E_FAILED_REQUEST, $previous);
    }
}
