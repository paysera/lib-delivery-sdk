<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Client;

use Exception;
use Paysera\DeliveryApi\MerchantClient\ClientFactory;
use Paysera\DeliveryApi\MerchantClient\Entity\Order;
use Paysera\DeliveryApi\MerchantClient\MerchantClient;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Exception\DeliveryOrderRequestException;
use Paysera\DeliverySdk\Exception\MerchantClientNotFoundException;
use Paysera\DeliverySdk\Facade\DeliveryOrderRequestAdapterFacade;
use Paysera\DeliverySdk\Service\LoggerInterface;

class DeliveryApiClient
{
    private const DEFAULT_BASE_URL = 'https://delivery-api.paysera.com/rest/v1/';

    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';

    private const ORDER_REQUEST_HANDLERS = [
        self::ACTION_CREATE => 'handleCreating',
        self::ACTION_UPDATE => 'handleUpdating',
    ];

    private DeliveryOrderRequestAdapterFacade $orderRequestAdapter;
    private LoggerInterface $logger;

    public function __construct(
        DeliveryOrderRequestAdapterFacade $orderRequestAdapter,
        LoggerInterface $logger
    ) {
        $this->orderRequestAdapter = $orderRequestAdapter;
        $this->logger = $logger;
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return Order
     * @throws DeliveryOrderRequestException
     * @see self::handleCreating()
     */
    public function sendOrderCreateRequest(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        return $this->sendOrderRequest(self::ACTION_CREATE, $deliveryOrderRequest);
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return Order
     * @throws DeliveryOrderRequestException
     * @see self::handleUpdating()
     */
    public function sendOrderUpdateRequest(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        return $this->sendOrderRequest(self::ACTION_UPDATE, $deliveryOrderRequest);
    }

    # region handling

    private function handleCreating(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        return $this
            ->initMerchantClient($deliveryOrderRequest)
            ->createOrder(
                $this->orderRequestAdapter->convertCreate($deliveryOrderRequest)
            )
        ;
    }

    private function handleUpdating(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        return $this
            ->initMerchantClient($deliveryOrderRequest)
            ->updateOrder(
                $deliveryOrderRequest->getOrder()->getDeliverOrderNumber(),
                $this->orderRequestAdapter->convertUpdate($deliveryOrderRequest)
            )
        ;
    }

    # endregion

    /**
     * @param string $action
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return Order
     * @throws DeliveryOrderRequestException
     */
    private function sendOrderRequest(string $action, PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        try {
            return $this->{self::ORDER_REQUEST_HANDLERS[$action]}($deliveryOrderRequest);
        } catch (Exception $exception) {
            $this->logException($action, $exception, $deliveryOrderRequest->getOrder());

            throw new DeliveryOrderRequestException($exception);
        }
    }

    private function initMerchantClient(PayseraDeliveryOrderRequest $deliveryOrderRequest): MerchantClient
    {
        $macId = $deliveryOrderRequest->getDeliverySettings()->getProjectId();
        $macSecret = $deliveryOrderRequest->getDeliverySettings()->getProjectPassword();

        if ($macId === null || $macSecret === null) {
            throw new MerchantClientNotFoundException();
        }

        $clientFactory = new ClientFactory([
            'base_url' => $this->getBaseUrl(),
            'mac' => [
                'mac_id' => $macId,
                'mac_secret' => $macSecret,
            ],
        ]);

        try {
            $merchantClient = $clientFactory->getMerchantClient();
        } catch (Exception $exception) {
            $this->logger->error('Cannot create merchant client', $exception);

            throw new MerchantClientNotFoundException();
        }

        return $merchantClient;
    }

    private function getBaseUrl(): string
    {
        $url = getenv('DELIVERY_API_URL');

        return !empty($url) ? $url : self::DEFAULT_BASE_URL;
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
