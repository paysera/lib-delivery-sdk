<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Entity;

use Paysera\Component\RestClientCommon\Entity\Entity;

class TerminalLocation extends Entity implements DeliveryTerminalLocationInterface
{
    private string $country;
    private string $city;
    private string $terminalId;
    private string $gatewayCode;

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setTerminalId(string $terminalId): self
    {
        $this->terminalId = $terminalId;

        return $this;
    }

    public function getTerminalId(): string
    {
        return $this->terminalId;
    }

    public function setDeliveryGatewayCode(string $gatewayCode): self
    {
        $this->gatewayCode = $gatewayCode;

        return $this;
    }

    public function getDeliveryGatewayCode(): string
    {
        return $this->gatewayCode;
    }
}
