<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\phpunit\Adapter;

use Paysera\DeliveryApi\MerchantClient\Entity\Address;
use Paysera\DeliveryApi\MerchantClient\Entity\Contact;
use Paysera\DeliveryApi\MerchantClient\Entity\ShipmentPointCreate;
use Paysera\DeliverySdk\Adapter\AddressAdapter;
use Paysera\DeliverySdk\Adapter\ContactAdapter;
use Paysera\DeliverySdk\Adapter\ShipmentPointAdapter;
use Paysera\DeliverySdk\Entity\DeliveryTerminalLocationInterface;
use Paysera\DeliverySdk\Entity\MerchantOrderAddressInterface;
use Paysera\DeliverySdk\Entity\MerchantOrderContactInterface;
use Paysera\DeliverySdk\Entity\MerchantOrderPartyInterface;
use PHPUnit\Framework\TestCase;

class ShipmentPointAdapterTest extends TestCase
{
    private ShipmentPointAdapter $shipmentPointAdapter;
    private ContactAdapter $contactAdapterMock;
    private AddressAdapter $addressAdapterMock;
    private MerchantOrderPartyInterface $partyDtoMock;
    private Contact $contactMock;
    private Address $addressMock;
    private MerchantOrderContactInterface $merchantContactMock;
    private MerchantOrderAddressInterface $merchantAddressMock;

    protected function setUp(): void
    {
        $this->contactAdapterMock = $this->createMock(ContactAdapter::class);
        $this->addressAdapterMock = $this->createMock(AddressAdapter::class);
        $this->partyDtoMock = $this->createMock(MerchantOrderPartyInterface::class);

        $this->addressMock = new Address();
        $this->contactMock = new Contact();

        $this->merchantContactMock = $this->createMock(MerchantOrderContactInterface::class);
        $this->merchantAddressMock = $this->createMock(MerchantOrderAddressInterface::class);

        $this->shipmentPointAdapter = new ShipmentPointAdapter(
            $this->contactAdapterMock,
            $this->addressAdapterMock
        );
    }

    /**
     * @dataProvider shipmentPointDataProvider
     */
    public function testConvert(
        ?DeliveryTerminalLocationInterface $terminalLocation,
        ?string $expectedParcelMachineId
    ): void {
        $this->partyDtoMock->method('getContact')->willReturn($this->merchantContactMock);
        $this->partyDtoMock->method('getAddress')->willReturn($this->merchantAddressMock);
        $this->partyDtoMock->method('getTerminalLocation')->willReturn($terminalLocation);

        $this->contactAdapterMock->method('convert')->willReturn($this->contactMock);
        $this->addressAdapterMock->method('convert')->willReturn($this->addressMock);

        $shipmentPoint = $this->shipmentPointAdapter->convert($this->partyDtoMock);

        $this->assertInstanceOf(ShipmentPointCreate::class, $shipmentPoint);
        $this->assertFalse($shipmentPoint->isSaved());
        $this->assertFalse($shipmentPoint->isDefaultContact());
        $this->assertSame($this->contactMock->getData(), $shipmentPoint->getContact()->getData());
        $this->assertSame($expectedParcelMachineId, $shipmentPoint->getParcelMachineId());
    }

    public function shipmentPointDataProvider(): iterable
    {
        $terminalLocation = $this->createMock(DeliveryTerminalLocationInterface::class);
        $terminalLocation->method('getTerminalId')->willReturn('T123');

        yield 'with terminal location' => [
            'terminalLocation' => $terminalLocation,
            'expectedParcelMachineId' => 'T123',
        ];

        yield 'without terminal location' => [
            'terminalLocation' => null,
            'expectedParcelMachineId' => null,
        ];
    }
}
