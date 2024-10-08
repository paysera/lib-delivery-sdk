<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Client;

use Paysera\DeliveryApi\MerchantClient\Entity\Order;
use Paysera\DeliverySdk\Adapter\DeliveryOrderRequestAdapterFacade;
use Paysera\DeliverySdk\Client\Provider\MerchantClientProvider;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;

class DeliveryOrderApiClient
{
    private const DEFAULT_BASE_URL = 'https://delivery-api.paysera.com/rest/v1/';

    private DeliveryOrderRequestAdapterFacade $orderRequestAdapter;
    private MerchantClientProvider $merchantClientProvider;

    public function __construct(
        DeliveryOrderRequestAdapterFacade $orderRequestAdapter,
        MerchantClientProvider $merchantClientProvider
    ) {
        $this->orderRequestAdapter = $orderRequestAdapter;
        $this->merchantClientProvider = $merchantClientProvider;
    }

    public function create(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        return $this
            ->merchantClientProvider
            ->getMerchantClient($deliveryOrderRequest->getDeliverySettings())
            ->createOrder(
                $this->orderRequestAdapter->convertCreate($deliveryOrderRequest)
            )
        ;
    }

    public function update(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        return $this
            ->merchantClientProvider
            ->getMerchantClient($deliveryOrderRequest->getDeliverySettings())
            ->updateOrder(
                $deliveryOrderRequest->getOrder()->getDeliverOrderNumber(),
                $this->orderRequestAdapter->convertUpdate($deliveryOrderRequest)
            )
        ;
    }

    public function get(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        return $this
            ->merchantClientProvider
            ->getMerchantClient($deliveryOrderRequest->getDeliverySettings())
            ->getOrder(
                $deliveryOrderRequest->getOrder()->getDeliverOrderId()
            )
        ;
    }
}
