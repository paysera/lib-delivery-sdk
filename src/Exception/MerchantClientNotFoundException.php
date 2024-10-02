<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Exception;

use LogicException;

class MerchantClientNotFoundException extends LogicException
{
    protected $message = 'Merchant Client Not Found';
}