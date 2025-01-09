<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Exception;

use Paysera\DeliverySdk\Entity\MerchantOrderInterface;

class UndefinedDeliveryOrderException extends BaseException
{
    protected $message = 'Delivery order id is not provided.';
    private MerchantOrderInterface $merchantOrder;

    public function __construct(MerchantOrderInterface $merchantOrder)
    {
        parent::__construct($this->message, self::E_UNDEFINED_DELIVERY_ORDER);

        $this->merchantOrder = $merchantOrder;
    }

    public function getMerchantOrder(): MerchantOrderInterface
    {
        return $this->merchantOrder;
    }
}
