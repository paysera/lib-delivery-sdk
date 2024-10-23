<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\phpunit\Adapter;

use Paysera\DeliveryApi\MerchantClient\Entity\Address;
use Paysera\DeliverySdk\Adapter\AddressAdapter;
use Paysera\DeliverySdk\Entity\MerchantOrderAddressInterface;
use PHPUnit\Framework\TestCase;

class AddressAdapterTest extends TestCase
{
    private AddressAdapter $addressAdapter;
    private MerchantOrderAddressInterface $addressDtoMock;

    protected function setUp(): void
    {
        $this->addressAdapter = new AddressAdapter();
        $this->addressDtoMock = $this->createMock(MerchantOrderAddressInterface::class);
    }

    /**
     * @dataProvider convertDataProvider
     */
    public function testConvert(array $expected): void
    {
        foreach ($expected as $method => $expectedValue) {
            $this->addressDtoMock->method($method)->willReturn($expectedValue);
        }

        $address = $this->addressAdapter->convert($this->addressDtoMock);

        $this->assertInstanceOf(Address::class, $address);
        foreach ($expected as $method => $expectedValue) {
            $this->assertSame($expectedValue, $address->{$method}());
        }
    }

    public function convertDataProvider(): iterable
    {
        $expected = [
            'getCountry' => 'LT',
            'getState' => 'Vilnius',
            'getCity' => 'Vilnius',
            'getStreet' => 'Gedimino ave.',
            'getPostalCode' => '01103',
            'getHouseNumber' => '5',
        ];

        yield 'with house number' => [
            $expected
        ];

        $expected['getHouseNumber'] = null;

        yield 'without house number' => [
            $expected
        ];
    }
}
