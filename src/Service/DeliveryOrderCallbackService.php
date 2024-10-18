<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Service;

use Paysera\DeliveryApi\MerchantClient\Entity\Order;
use Paysera\DeliverySdk\Client\DeliveryApiClient;
use Paysera\DeliverySdk\Dto\ObjectStateDto;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Entity\PayseraDeliverySettingsInterface;
use Paysera\DeliverySdk\Entity\TerminalLocation;
use Paysera\DeliverySdk\Exception\DeliveryGatewayNotFoundException;
use Paysera\DeliverySdk\Exception\DeliveryOrderRequestException;
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

    public function __construct(
        DeliveryApiClient $apiClient,
        ObjectStateService $objectStateService,
        MerchantOrderRepositoryInterface $merchantOrderRepository,
        MerchantOrderLoggerInterface $merchantOrderLogger,
        DeliveryGatewayRepositoryInterface $deliveryGatewayRepository,
        DeliveryGatewayUtils $gatewayUtils
    ) {
        $this->apiClient = $apiClient;
        $this->merchantOrderRepository = $merchantOrderRepository;
        $this->objectStateService = $objectStateService;
        $this->merchantOrderLogger = $merchantOrderLogger;
        $this->deliveryGatewayRepository = $deliveryGatewayRepository;
        $this->gatewayUtils = $gatewayUtils;
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return MerchantOrderInterface
     * @throws DeliveryOrderRequestException
     * @throws DeliveryGatewayNotFoundException
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
            $diffState,
            'shipping.',
        );

        $this->merchantOrderRepository->save($merchantOrder);
    }

    private function updateDeliveryGateway(MerchantOrderInterface $merchantOrder, Order $deliveryOrder): void
    {
        $gatewayCode = $this->gatewayUtils->getGatewayCodeFromDeliveryOrder($deliveryOrder);
        $deliveryGateway = $this->deliveryGatewayRepository->findPayseraGateway($gatewayCode);

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

        $this->merchantOrderRepository->save($merchantOrder);
    }

    private function updateParcelMachine(
        MerchantOrderInterface $order,
        Order $deliveryOrder,
        string $newDeliveryGatewayCode
    ): void {
        $parcelMachine = $deliveryOrder->getReceiver()->getParcelMachine();

        if ($parcelMachine === null) {
            return;
        }

        $address = $parcelMachine->getAddress();

        $newTerminalLocation = (new TerminalLocation())
            ->setCountry($address->getCountry())
            ->setCity($address->getCity())
            ->setTerminalId($parcelMachine->getId())
            ->setDeliveryGatewayCode($newDeliveryGatewayCode)
        ;
        $oldTerminalLocation = $order->getShipping()->getTerminalLocation();

        if (
            $oldTerminalLocation === null
            || $oldTerminalLocation->getTerminalId() === $newTerminalLocation->getTerminalId()
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

        $this->merchantOrderLogger->logShippingChanges($order, $currData, $prevData);
    }
}
