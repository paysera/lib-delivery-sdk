<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Client;

use Closure;
use Exception;
use Paysera\DeliveryApi\MerchantClient\Entity\Order;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Exception\DeliveryOrderRequestException;
use Paysera\DeliverySdk\Service\DeliveryLoggerInterface;

class DeliveryApiClient
{
    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_PREPAID = 'prepaid';
    public const ACTION_GET = 'get';

    private DeliveryOrderApiClient $orderRequestHandler;
    private DeliveryLoggerInterface $logger;

    public function __construct(
        DeliveryOrderApiClient $orderRequestHandler,
        DeliveryLoggerInterface $logger
    ) {
        $this->orderRequestHandler = $orderRequestHandler;
        $this->logger = $logger;
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return Order
     * @throws DeliveryOrderRequestException
     */
    public function postOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        return $this->sendOrderRequest(
            self::ACTION_CREATE,
            fn (PayseraDeliveryOrderRequest $deliveryOrderRequest) => $this
                ->orderRequestHandler
                ->create($deliveryOrderRequest),
            $deliveryOrderRequest
        );
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return Order
     * @throws DeliveryOrderRequestException
     */
    public function patchOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        return $this->sendOrderRequest(
            self::ACTION_UPDATE,
            fn (PayseraDeliveryOrderRequest $deliveryOrderRequest) => $this
                ->orderRequestHandler
                ->update($deliveryOrderRequest),
            $deliveryOrderRequest
        );
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return Order
     * @throws DeliveryOrderRequestException
     */
    public function getOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        return $this->sendOrderRequest(
            self::ACTION_GET,
            fn (PayseraDeliveryOrderRequest $deliveryOrderRequest) => $this
                ->orderRequestHandler
                ->get($deliveryOrderRequest),
            $deliveryOrderRequest
        );
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return Order
     * @throws DeliveryOrderRequestException
     */
    public function prepaidOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        return $this->sendOrderRequest(
            self::ACTION_PREPAID,
            fn (PayseraDeliveryOrderRequest $deliveryOrderRequest) => $this->orderRequestHandler
                ->prepaid($deliveryOrderRequest),
            $deliveryOrderRequest
        );
    }

    /**
     * @param string $action
     * @param Closure $handler
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return Order
     * @throws DeliveryOrderRequestException
     */
    private function sendOrderRequest(
        string $action,
        Closure $handler,
        PayseraDeliveryOrderRequest $deliveryOrderRequest
    ): Order {
        try {
            return $handler($deliveryOrderRequest);
        } catch (Exception $exception) {
            $this->logException($action, $exception, $deliveryOrderRequest->getOrder());

            throw new DeliveryOrderRequestException($exception);
        }
    }

    private function logException(string $action, Exception $exception, MerchantOrderInterface $order): void
    {
        $this->logger->error(
            sprintf(
                'Cannot perform operation \'%s\' on delivery order for order id %s.',
                $action,
                $order->getNumber(),
            ),
            $exception
        );
    }
}
