<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\phpunit\Entity;

use Paysera\DeliverySdk\Entity\TerminalLocation;
use PHPUnit\Framework\TestCase;

class TerminalLocationTest extends TestCase
{
    protected TerminalLocation $terminalLocation;

    protected function setUp(): void
    {
        $this->terminalLocation = new TerminalLocation();
    }

    public function testSetAndGetCountry(): void
    {
        $country = 'Lithuania';
        $this->terminalLocation->setCountry($country);
        $this->assertSame($country, $this->terminalLocation->getCountry());
    }

    public function testSetAndGetCity(): void
    {
        $city = 'Vilnius';
        $this->terminalLocation->setCity($city);
        $this->assertSame($city, $this->terminalLocation->getCity());
    }

    public function testSetAndGetTerminalId(): void
    {
        $terminalId = 'T12345';
        $this->terminalLocation->setTerminalId($terminalId);
        $this->assertSame($terminalId, $this->terminalLocation->getTerminalId());
    }

    public function testSetAndGetDeliveryGatewayCode(): void
    {
        $gatewayCode = 'DHL';
        $this->terminalLocation->setDeliveryGatewayCode($gatewayCode);
        $this->assertSame($gatewayCode, $this->terminalLocation->getDeliveryGatewayCode());
    }

    public function testChainedSetters(): void
    {
        $this->terminalLocation
            ->setCountry('Estonia')
            ->setCity('Tallinn')
            ->setTerminalId('T54321')
            ->setDeliveryGatewayCode('FedEx');

        $this->assertSame('Estonia', $this->terminalLocation->getCountry());
        $this->assertSame('Tallinn', $this->terminalLocation->getCity());
        $this->assertSame('T54321', $this->terminalLocation->getTerminalId());
        $this->assertSame('FedEx', $this->terminalLocation->getDeliveryGatewayCode());
    }
}
