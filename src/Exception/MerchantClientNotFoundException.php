<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Exception;

class MerchantClientNotFoundException extends BaseException
{
    protected $message = 'Merchant Client Not Found';

    public function __construct()
    {
        parent::__construct($this->message, self::E_MERCHANT_CLIENT_NOT_FOUND);
    }
}
