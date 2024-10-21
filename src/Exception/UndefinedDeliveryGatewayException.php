<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Exception;

class UndefinedDeliveryGatewayException extends BaseException
{
    protected $message = 'The delivery order doesn\'t contain a shipping method or gateway.';

    public function __construct()
    {
        parent::__construct($this->message, self::E_UNDEFINED_DELIVERY_GATEWAY);
    }
}
