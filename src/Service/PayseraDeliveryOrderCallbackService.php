<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Service;

use Paysera\DeliveryApi\MerchantClient\Entity\Order;
use Paysera\DeliverySdk\Client\DeliveryApiClient;
use Paysera\DeliverySdk\Client\Provider\MerchantClientProvider;
use Paysera\DeliverySdk\Dto\ObjectStateDto;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Exception\DeliveryOrderRequestException;
use Paysera\DeliverySdk\Repository\MerchantOrderRepositoryInterface;
use Paysera\Entity\PayseraDeliverySettings;
use Paysera\Entity\PayseraPaths;
use Paysera\Helper\WCOrderUpdateHelperInterface;

class PayseraDeliveryOrderCallbackService
{
    private DeliveryApiClient $apiClient;
    private MerchantOrderRepositoryInterface $merchantOrderRepository;
    private ObjectStateService $objectStateService;
    private MerchantOrderLoggerInterface $merchantOrderLogger;

    public function __construct(
        DeliveryApiClient $apiClient,
        ObjectStateService $objectStateService,
        MerchantOrderRepositoryInterface $merchantOrderRepository,
        MerchantOrderLoggerInterface $merchantOrderLogger
    ) {
        $this->apiClient = $apiClient;
        $this->merchantOrderRepository = $merchantOrderRepository;
        $this->objectStateService = $objectStateService;
        $this->merchantOrderLogger = $merchantOrderLogger;
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return MerchantOrderInterface
     * @throws DeliveryOrderRequestException
     */
    public function updateMerchantOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): MerchantOrderInterface
    {
        $deliveryOrder = $this->apiClient->getOrder($deliveryOrderRequest);

        $this->updateShippingInfo($deliveryOrderRequest->getOrder(), $deliveryOrder);

        return $deliveryOrderRequest->getOrder();
    }

    private function updateShippingInfo(MerchantOrderInterface $merchantOrder, Order $deliveryOrder): void
    {
        $receiver = $deliveryOrder->getReceiver();

        if ($receiver === null) {
            return;
        }

        $contact = $receiver->getContact();

        if ($contact === null) {
            return;
        }

        $merchantShipping = $merchantOrder->getShipping();

        $fieldsMap = [
            'party.phone' => 'contact.phone',
            'party.email' => 'contact.email',
            'address.country' => 'address.country',
            'address.city' => 'address.city',
            'address.street' => 'address.street',
            'address.postalCode' => 'address.postalCode',
            'address.houseNumber' => 'address.houseNumber',
        ];

        $deliveryOrderContactState = $this->objectStateService->getState($contact, array_keys($fieldsMap));
        $merchantOrderShippingState = $this->objectStateService->getState($merchantShipping, array_values($fieldsMap));
        $diffState = $this->objectStateService->diffState(
            $deliveryOrderContactState,
            $merchantOrderShippingState,
            $fieldsMap
        );

        if (empty($diffState->getState())) {
            return;
        }

        $this->objectStateService->setState($diffState, $merchantShipping);

        $this->logShippingChanges(
            $merchantOrder,
            $merchantOrderShippingState,
            $diffState
        );

        $this->merchantOrderRepository->save($merchantOrder);
    }

    private function logShippingChanges(
        MerchantOrderInterface $order,
        ObjectStateDto $previousState,
        ObjectStateDto $currentState,
        string $prefix = ''
    ): void {
        $prevData = [];
        $currData = [];

        foreach ($currentState->getState() as $key => $value) {
            $prevData[$prefix . $key] = $previousState->getState()[$key];
            $currData[$prefix . $key] = $value;
        }

        $this->merchantOrderLogger->logShippingChanges($order, $currData, $prevData);
    }
}
