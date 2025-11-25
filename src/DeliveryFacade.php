<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk;

use Paysera\DeliveryApi\MerchantClient\Entity\ProjectCredentials;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Exception\MerchantClientNotFoundException;
use Paysera\DeliverySdk\Exception\RateLimitExceededException;
use Paysera\DeliverySdk\Service\DeliveryOrderCallbackService;
use Paysera\DeliverySdk\Service\DeliveryOrderService;
use Paysera\DeliverySdk\Exception\CredentialsValidationException;
use Paysera\DeliverySdk\Exception\DeliveryOrderRequestException;
use Paysera\DeliverySdk\Exception\DeliveryGatewayNotFoundException;

class DeliveryFacade
{
    private DeliveryOrderService $deliveryOrderService;
    private DeliveryOrderCallbackService $deliveryOrderCallbackService;

    public function __construct(
        DeliveryOrderService $deliveryOrderService,
        DeliveryOrderCallbackService $deliveryOrderCallbackService
    ) {
        $this->deliveryOrderService = $deliveryOrderService;
        $this->deliveryOrderCallbackService = $deliveryOrderCallbackService;
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return MerchantOrderInterface|null
     * @throws DeliveryOrderRequestException
     */
    public function createDeliveryOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): ?MerchantOrderInterface
    {
        return $this->deliveryOrderService->createDeliveryOrder($deliveryOrderRequest);
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return MerchantOrderInterface|null
     * @throws DeliveryOrderRequestException
     */
    public function updateDeliveryOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): ?MerchantOrderInterface
    {
        return $this->deliveryOrderService->updateDeliveryOrder($deliveryOrderRequest);
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return MerchantOrderInterface
     * @throws DeliveryOrderRequestException
     * @throws DeliveryGatewayNotFoundException
     */
    public function updateMerchantOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): MerchantOrderInterface
    {
        return $this->deliveryOrderCallbackService->updateMerchantOrder($deliveryOrderRequest);
    }

    /**
     * @throws DeliveryOrderRequestException
     */
    public function prepaidDeliveryOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): ?MerchantOrderInterface
    {
        return $this->deliveryOrderService->prepaidDeliveryOrder($deliveryOrderRequest);
    }

    /**
     * @param ProjectCredentials $credentials
     * @return bool
     * @throws CredentialsValidationException
     * @throws RateLimitExceededException
     * @throws MerchantClientNotFoundException
     */
    public function validateCredentials(ProjectCredentials $credentials): bool
    {
        return $this->deliveryOrderService->validateCredentials($credentials);
    }
}
