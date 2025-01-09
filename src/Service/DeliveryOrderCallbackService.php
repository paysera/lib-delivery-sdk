<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Service;

use Paysera\DeliveryApi\MerchantClient\Entity\Order;
use Paysera\DeliverySdk\Client\DeliveryApiClient;
use Paysera\DeliverySdk\Dto\ObjectStateDto;
use Paysera\DeliverySdk\Entity\DeliveryTerminalLocationFactoryInterface;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Entity\PayseraDeliverySettingsInterface;
use Paysera\DeliverySdk\Exception\DeliveryGatewayNotFoundException;
use Paysera\DeliverySdk\Exception\DeliveryOrderRequestException;
use Paysera\DeliverySdk\Exception\UndefinedDeliveryGatewayException;
use Paysera\DeliverySdk\Repository\DeliveryGatewayRepositoryInterface;
use Paysera\DeliverySdk\Repository\MerchantOrderRepositoryInterface;
use Paysera\DeliverySdk\Util\DeliveryGatewayUtils;

class DeliveryOrderCallbackService
{
    private DeliveryApiClient $apiClient;
    private MerchantOrderRepositoryInterface $merchantOrderRepository;
    private ObjectStateService $objectStateService;
    private MerchantOrderLoggerInterface $merchantOrderLogger;
    private DeliveryGatewayRepositoryInterface $deliveryGatewayRepository;
    private DeliveryGatewayUtils $gatewayUtils;
    private DeliveryTerminalLocationFactoryInterface $deliveryTerminalLocationFactory;

    public function __construct(
        DeliveryApiClient $apiClient,
        ObjectStateService $objectStateService,
        MerchantOrderRepositoryInterface $merchantOrderRepository,
        MerchantOrderLoggerInterface $merchantOrderLogger,
        DeliveryGatewayRepositoryInterface $deliveryGatewayRepository,
        DeliveryGatewayUtils $gatewayUtils,
        DeliveryTerminalLocationFactoryInterface $deliveryTerminalLocationFactory
    ) {
        $this->apiClient = $apiClient;
        $this->merchantOrderRepository = $merchantOrderRepository;
        $this->objectStateService = $objectStateService;
        $this->merchantOrderLogger = $merchantOrderLogger;
        $this->deliveryGatewayRepository = $deliveryGatewayRepository;
        $this->gatewayUtils = $gatewayUtils;
        $this->deliveryTerminalLocationFactory = $deliveryTerminalLocationFactory;
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return MerchantOrderInterface
     * @throws DeliveryOrderRequestException
     * @throws DeliveryGatewayNotFoundException
     * @throws UndefinedDeliveryGatewayException
     */
    public function updateMerchantOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): MerchantOrderInterface
    {
        $deliveryOrder = $this->apiClient->getOrder($deliveryOrderRequest);

        $this->updateShippingInfo($deliveryOrderRequest->getOrder(), $deliveryOrder);
        $this->updateDeliveryGateway($deliveryOrderRequest->getOrder(), $deliveryOrder);

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
             'contact.phone' => 'party.phone',
             'contact.email' => 'party.email',
             'address.country' => 'address.country',
             'address.city' => 'address.city',
             'address.street' => 'address.street',
             'address.postalCode' => 'address.postal_code',
             'address.houseNumber' => 'address.house_number',
        ];

        $deliveryOrderContactState = $this->objectStateService->getState($contact, array_values($fieldsMap));
        $merchantOrderShippingState = $this->objectStateService->getState($merchantShipping, array_keys($fieldsMap));

        $diffState = $this->objectStateService->diffState(
            $deliveryOrderContactState,
            $merchantOrderShippingState,
            array_flip($fieldsMap)
        );

        if (empty($diffState->getState())) {
            return;
        }

        $newState = $this->objectStateService->transformState($diffState, array_flip($fieldsMap));
        $this->objectStateService->setState($newState, $merchantShipping);

        $this->logShippingChanges(
            $merchantOrder,
            $merchantOrderShippingState,
            $newState,
            'shipping.',
        );

        $this->merchantOrderRepository->save($merchantOrder);
    }

    private function updateDeliveryGateway(MerchantOrderInterface $merchantOrder, Order $deliveryOrder): void
    {
        $gatewayCode = $this->gatewayUtils->getGatewayCodeFromDeliveryOrder($deliveryOrder);

        if ($gatewayCode === null) {
            throw new UndefinedDeliveryGatewayException();
        }

        $deliveryGateway = $this->deliveryGatewayRepository->findPayseraGatewayForDeliveryOrder($deliveryOrder);

        if ($deliveryGateway === null) {
            throw new DeliveryGatewayNotFoundException($gatewayCode, $merchantOrder->getNumber());
        }

        $actualDeliveryGateway = $merchantOrder->getDeliveryGateway();

        if ($actualDeliveryGateway === null) {
            return;
        }

        if ($actualDeliveryGateway->getCode() !== $deliveryGateway->getCode()) {
            $merchantOrder->setDeliveryGateway($deliveryGateway);
        }

        $shippingMethod = $deliveryOrder->getShipmentMethod();

        if (
            $shippingMethod !== null
            && $shippingMethod->getReceiverCode() === PayseraDeliverySettingsInterface::TYPE_PARCEL_MACHINE
        ) {
            $this->updateParcelMachine(
                $merchantOrder,
                $deliveryOrder,
                $deliveryGateway->getCode(),
            );
        } else {
            $merchantOrder->getShipping()->setTerminalLocation(null);
        }

        $this->merchantOrderLogger->logDeliveryGatewayChanges($merchantOrder, $actualDeliveryGateway, $deliveryGateway);

        $this->merchantOrderRepository->save($merchantOrder);
    }

    private function updateParcelMachine(
        MerchantOrderInterface $order,
        Order $deliveryOrder,
        string $newDeliveryGatewayCode
    ): void {
        $receiver = $deliveryOrder->getReceiver();

        if ($receiver === null) {
            return;
        }

        $parcelMachine = $receiver->getParcelMachine();

        if ($parcelMachine === null) {
            return;
        }

        $address = $parcelMachine->getAddress();

        $newTerminalLocation = $this->deliveryTerminalLocationFactory
            ->create()
            ->setCountry($address->getCountry())
            ->setCity((string)$address->getCity())
            ->setTerminalId($parcelMachine->getId())
            ->setDeliveryGatewayCode($this->gatewayUtils->resolveDeliveryGatewayCode($newDeliveryGatewayCode))
        ;
        $oldTerminalLocation = $order->getShipping()->getTerminalLocation();

        if (
            $oldTerminalLocation !== null
            && $oldTerminalLocation->getTerminalId() === $newTerminalLocation->getTerminalId()
        ) {
            return;
        }

        $order->getShipping()->setTerminalLocation($newTerminalLocation);
        $this->merchantOrderRepository->save($order);

        $this->merchantOrderLogger->logDeliveryTerminalLocationChanges(
            $order,
            $oldTerminalLocation,
            $newTerminalLocation
        );
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

        $this->merchantOrderLogger->logShippingChanges($order, $prevData, $currData);
    }
}
