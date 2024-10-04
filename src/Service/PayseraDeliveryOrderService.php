<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Service;

use Paysera\DeliveryApi\MerchantClient\Entity\Order;
use Paysera\DeliverySdk\Client\DeliveryApiClient;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Facade\DeliveryOrderRequestAdapterFacade;
use Paysera\DeliverySdk\Repository\MerchantOrderRepositoryInterface;
use Paysera\Helper\PayseraDeliveryOrderRequestHelper;

class PayseraDeliveryOrderService
{
    private const LOG_MESSAGE_STARTED = 'Attempting to perform operation \'%s\' of delivery order for order id %s with project id: %s';
    private const LOG_MESSAGE_COMPLETED = 'Operation \'%s\' of delivery order %s for order id %d is completed.';

    private MerchantOrderRepositoryInterface $merchantOrderRepository;
    private DeliveryApiClient $deliveryApiClient;
    private LoggerInterface $logger;

    public function __construct(
        MerchantOrderRepositoryInterface $merchantOrderRepository,
        DeliveryApiClient $deliveryApiClient,
        LoggerInterface $logger
    ) {
        $this->deliveryApiClient = $deliveryApiClient;
        $this->logger = $logger;
        $this->merchantOrderRepository = $merchantOrderRepository;
    }

    public function createDeliveryOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): ?MerchantOrderInterface
    {
        $this->logStepStarted(DeliveryApiClient::ACTION_CREATE, $deliveryOrderRequest);
        $deliveryOrder = $this->handleCreating($deliveryOrderRequest);
        $this->logStepCompleted(DeliveryApiClient::ACTION_CREATE, $deliveryOrderRequest, $deliveryOrder);

        return $deliveryOrderRequest->getOrder();
    }

    public function updateDeliveryOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): ?MerchantOrderInterface
    {
        $this->logStepStarted(DeliveryApiClient::ACTION_UPDATE, $deliveryOrderRequest);
        $deliveryOrder = $this->deliveryApiClient->sendOrderUpdateRequest($deliveryOrderRequest);
        $this->logStepCompleted(DeliveryApiClient::ACTION_UPDATE, $deliveryOrderRequest, $deliveryOrder);

        return $deliveryOrderRequest->getOrder();
    }

    #region Handling

    private function handleCreating(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        $order = $deliveryOrderRequest->getOrder();
        $deliveryOrder = $this->deliveryApiClient->sendOrderCreateRequest($deliveryOrderRequest);

        $order->setDeliverOrderId($deliveryOrder->getId());
        $order->setDeliverOrderNumber($deliveryOrder->getNumber());
        $this->merchantOrderRepository->save($order);

        return $deliveryOrder;
    }

    #endregion

    #region Service Methods

    private function logStepStarted(string $action, PayseraDeliveryOrderRequest $request): void
    {
        $this->logger->info(
            sprintf(
                self::LOG_MESSAGE_STARTED,
                $action,
                $request->getOrder()->getNumber(),
                $request->getDeliverySettings()->getProjectId()
            )
        );
    }

    private function logStepCompleted(
        string $action,
        PayseraDeliveryOrderRequest $deliveryOrderRequest,
        Order $deliveryOrder
    ): void {
        $order = $deliveryOrderRequest->getOrder();
        $orderNumber = $deliveryOrder->getNumber();

        $this->logger->info(
            sprintf(
                self::LOG_MESSAGE_COMPLETED,
                $action,
                $orderNumber,
                $order->getNumber(),
            )
        );
    }

    #endregion
}
