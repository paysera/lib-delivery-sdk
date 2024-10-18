<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\Service;

use ArrayAccess;
use Paysera\DeliveryApi\MerchantClient\Entity\Address;
use Paysera\DeliveryApi\MerchantClient\Entity\Contact;
use Paysera\DeliveryApi\MerchantClient\Entity\Order;
use Paysera\DeliveryApi\MerchantClient\Entity\ParcelMachine;
use Paysera\DeliveryApi\MerchantClient\Entity\Party;
use Paysera\DeliveryApi\MerchantClient\Entity\ShipmentMethod;
use Paysera\DeliveryApi\MerchantClient\Entity\ShipmentPoint;
use Paysera\DeliverySdk\Client\DeliveryApiClient;
use Paysera\DeliverySdk\Dto\ObjectStateDto;
use Paysera\DeliverySdk\Entity\DeliveryTerminalLocationInterface;
use Paysera\DeliverySdk\Entity\MerchantOrderAddressInterface;
use Paysera\DeliverySdk\Entity\MerchantOrderContactInterface;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\MerchantOrderPartyInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryGatewayInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Exception\DeliveryGatewayNotFoundException;
use Paysera\DeliverySdk\Repository\DeliveryGatewayRepositoryInterface;
use Paysera\DeliverySdk\Repository\MerchantOrderRepositoryInterface;
use Paysera\DeliverySdk\Service\DeliveryOrderCallbackService;
use Paysera\DeliverySdk\Service\MerchantOrderLoggerInterface;
use Paysera\DeliverySdk\Service\ObjectStateService;
use Paysera\DeliverySdk\Util\DeliveryGatewayUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeliveryOrderCallbackServiceTest extends TestCase
{
    private DeliveryOrderCallbackService $service;
    private DeliveryApiClient $apiClient;
    private MerchantOrderRepositoryInterface $merchantOrderRepository;
    private ObjectStateService $objectStateService;
    private DeliveryGatewayRepositoryInterface $deliveryGatewayRepository;
    private DeliveryGatewayUtils $gatewayUtils;

    protected function setUp(): void
    {
        $this->apiClient = $this->createMock(DeliveryApiClient::class);
        $this->merchantOrderRepository = $this->createMock(MerchantOrderRepositoryInterface::class);
        $this->objectStateService = $this->createMock(ObjectStateService::class);
        $merchantOrderLogger = $this->createMock(MerchantOrderLoggerInterface::class);
        $this->deliveryGatewayRepository = $this->createMock(DeliveryGatewayRepositoryInterface::class);
        $this->gatewayUtils = $this->createMock(DeliveryGatewayUtils::class);

        $this->service = new DeliveryOrderCallbackService(
            $this->apiClient,
            $this->objectStateService,
            $this->merchantOrderRepository,
            $merchantOrderLogger,
            $this->deliveryGatewayRepository,
            $this->gatewayUtils
        );
    }

    public function testUpdateMerchantOrderUpdatesShippingInfo(): void
    {
        $deliveryGateway = $this->createMock(PayseraDeliveryGatewayInterface::class);

        $deliveryOrderMock = $this->mockDeliveryOrder();
        $this->apiClient->method('getOrder')->willReturn($deliveryOrderMock);

        $deliveryOrderRequest = $this->createMock(PayseraDeliveryOrderRequest::class);

        $merchantOrder = $this->mockMerchantOrder();
        $deliveryOrderRequest->method('getOrder')->willReturn($merchantOrder);

        $diffState = $this->createMock(ObjectStateDto::class);
        $diffState->method('getState')->willReturn(
            [
                'contact.phone' => 'old_phone',
                'contact.email' => 'old@email',
                'address.country' => 'OldCountry',
                'address.city' => 'OldCity',
                'address.street' => 'OldStreet',
                'address.postalCode' => '05566',
                'address.houseNumber' => '1223',
            ]
        );

        $this->objectStateService->expects($this->exactly(2))
            ->method('getState')
            ->willReturnCallback(
                function (ArrayAccess $sourceObject) use ($deliveryOrderMock, $merchantOrder) {
                    switch ($sourceObject) {
                        case $deliveryOrderMock->getReceiver()->getContact():
                            return new ObjectStateDto(
                                [
                                    'party.phone' => 'new_phone',
                                    'party.email' => 'new@email',
                                    'address.country' => 'NewCountry',
                                    'address.city' => 'NewCity',
                                    'address.street' => 'NewStreet',
                                    'address.postalCode' => '06677',
                                    'address.houseNumber' => '2334',
                                ]
                            );
                        case $merchantOrder->getShipping():
                            return new ObjectStateDto(
                                [
                                    'contact.phone' => 'old_phone',
                                    'contact.email' => 'old@email',
                                    'address.country' => 'OldCountry',
                                    'address.city' => 'OldCity',
                                    'address.street' => 'OldStreet',
                                    'address.postalCode' => '05566',
                                    'address.houseNumber' => '1223',
                                ]
                            );
                    }
                }
            );

        $this->objectStateService->method('diffState')->willReturn($diffState);
        $this->merchantOrderRepository->expects($this->once())->method('save')->with($merchantOrder);
        $this->deliveryGatewayRepository->method('findPayseraGateway')->willReturn($deliveryGateway);

        $this->service->updateMerchantOrder($deliveryOrderRequest);
    }

    public function testUpdateMerchantOrderUpdatesDeliveryGateway(): void
    {
        /** @var MerchantOrderInterface|MockObject $merchantOrder */
        $merchantOrder = $this->mockMerchantOrder();
        $merchantDeliveryGateway = $this->createMock(PayseraDeliveryGatewayInterface::class);
        $merchantDeliveryGateway->method('getCode')->willReturn('fedex');
        $merchantDeliveryGateway->method('getName')->willReturn('FedEx Terminal');
        $merchantDeliveryGateway->method('getFee')->willReturn(1.00);

        $merchantOrder->method('getDeliveryGateway')->willReturn($merchantDeliveryGateway);

        $deliveryOrder = $this->mockDeliveryOrder();
        $gatewayCode = 'gateway123';
        $deliveryGateway = $this->createMock(PayseraDeliveryGatewayInterface::class);
        $deliveryGateway->method('getCode')->willReturn($gatewayCode);
        $deliveryGateway->method('getName')->willReturn('gateway123 Terminal');
        $deliveryGateway->method('getFee')->willReturn(1.00);

        $deliveryOrderRequest = $this->createMock(PayseraDeliveryOrderRequest::class);

        $deliveryOrderRequest->method('getOrder')->willReturn($merchantOrder);
        $this->apiClient->method('getOrder')->willReturn($deliveryOrder);
        $this->gatewayUtils->method('getGatewayCodeFromDeliveryOrder')->willReturn($gatewayCode);
        $this->deliveryGatewayRepository->method('findPayseraGateway')->willReturn($deliveryGateway);

        $this->merchantOrderRepository->expects($this->once())->method('save')->with($merchantOrder);

        $this->service->updateMerchantOrder($deliveryOrderRequest);
    }

    public function testUpdateMerchantOrderThrowsExceptionForMissingGateway(): void
    {
        $merchantOrder = $this->createMock(MerchantOrderInterface::class);
        $deliveryOrder = $this->createMock(Order::class);
        $gatewayCode = 'missing_gateway';

        $deliveryOrderRequest = $this->createMock(PayseraDeliveryOrderRequest::class);

        $deliveryOrderRequest->method('getOrder')->willReturn($merchantOrder);
        $this->apiClient->method('getOrder')->willReturn($deliveryOrder);
        $this->gatewayUtils->method('getGatewayCodeFromDeliveryOrder')->willReturn($gatewayCode);
        $this->deliveryGatewayRepository->method('findPayseraGateway')->willReturn(null);

        $this->expectException(DeliveryGatewayNotFoundException::class);

        $this->service->updateMerchantOrder($deliveryOrderRequest);
    }

    private function mockMerchantOrder(): MerchantOrderInterface
    {
        $merchantOrder = $this->createMock(MerchantOrderInterface::class);
        $contact = $this->createMock(MerchantOrderContactInterface::class);
        $contact->method('getPhone')->willReturn('old_phone');
        $contact->method('getEmail')->willReturn('old@email');

        $address = $this->createMock(MerchantOrderAddressInterface::class);
        $address->method('getCountry')->willReturn('OldCountry');
        $address->method('getCity')->willReturn('OldCity');
        $address->method('getState')->willReturn('OldState');
        $address->method('getStreet')->willReturn('OldStreet');
        $address->method('getHouseNumber')->willReturn('1223');
        $address->method('getPostalCode')->willReturn('05566');

        $terminalLocation = $this->createMock(DeliveryTerminalLocationInterface::class);
        $terminalLocation->method('getTerminalId')->willReturn('OLD123');

        $shipping = $this->createMock(MerchantOrderPartyInterface::class);
        $shipping->method('getContact')->willReturn($contact);
        $shipping->method('getAddress')->willReturn($address);

        $merchantOrder->method('getShipping')->willReturn($shipping);

        return $merchantOrder;
    }

    private function mockDeliveryOrder(): Order
    {
        $deliveryOrderMock = $this->createMock(Order::class);

        $address = $this->createMock(Address::class);
        $address->method('getCountry')->willReturn('NewCountry');
        $address->method('getCity')->willReturn('NewCity');
        $address->method('getState')->willReturn('NewState');
        $address->method('getStreet')->willReturn('NewStreet');
        $address->method('getHouseNumber')->willReturn('2334');
        $address->method('getPostalCode')->willReturn('06677');

        $party = $this->createMock(Party::class);
        $party->method('getPhone')->willReturn('new_phone');
        $party->method('getEmail')->willReturn('new@email');

        $contact = $this->createMock(Contact::class);
        $contact->method('getAddress')->willReturn($address);
        $contact->method('getParty')->willReturn($party);

        $parcelMachine = $this->createMock(ParcelMachine::class);
        $parcelMachine->method('getId')->willReturn('NEW123');
        $parcelMachine->method('getAddress')->willReturn($address);

        $receiver = $this->createMock(ShipmentPoint::class);
        $receiver->method('getContact')->willReturn($contact);
        $receiver->method('getParcelMachine')->willReturn($parcelMachine);

        $deliveryOrderMock->method('getReceiver')->willReturn($receiver);

        $shippingMethod = $this->createMock(ShipmentMethod::class);
        $shippingMethod->method('getReceiverCode')->willReturn('parcel-machine');

        $deliveryOrderMock->method('getShipmentMethod')->willReturn($shippingMethod);

        return $deliveryOrderMock;
    }
}
