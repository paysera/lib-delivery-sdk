<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Adapter;

use Paysera\DeliveryApi\MerchantClient\Entity\ShipmentPointCreate;
use Paysera\DeliverySdk\Entity\MerchantOrderPartyInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliverySettingsInterface;

class ShipmentPointAdapter
{
    private ContactAdapter $contactAdapter;
    private AddressAdapter $addressAdapter;

    public function __construct(
        ContactAdapter $contactAdapter,
        AddressAdapter $addressAdapter
    ) {
        $this->contactAdapter = $contactAdapter;
        $this->addressAdapter = $addressAdapter;
    }

    public function convert(
        MerchantOrderPartyInterface $partyDto,
        PayseraDeliverySettingsInterface $deliverySettings,
        string $type
    ): ShipmentPointCreate {
        $contact = $this->contactAdapter
            ->convert($partyDto->getContact())
            ->setAddress($this->addressAdapter->convert($partyDto->getAddress()));
        ;

        $shipmentPoint = (new ShipmentPointCreate())
            ->setType($type)
            ->setSaved(false)
            ->setDefaultContact(false)
            ->setContact($contact)
        ;

        if ($deliverySettings->getResolvedProjectId() !== null) {
            $shipmentPoint->setProjectId($deliverySettings->getResolvedProjectId());
        }

        if ($partyDto->getTerminalLocation() !== null) {
            $shipmentPoint->setParcelMachineId(
                $partyDto->getTerminalLocation()->getTerminalId()
            );
        }

        return $shipmentPoint;
    }
}
