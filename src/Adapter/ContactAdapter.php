<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Adapter;

use Paysera\DeliveryApi\MerchantClient\Entity\Contact;
use Paysera\DeliveryApi\MerchantClient\Entity\Party;
use Paysera\DeliverySdk\Entity\MerchantOrderContactInterface;

class ContactAdapter
{
    public function convert(MerchantOrderContactInterface $contact): Contact
    {
        return (new Contact())
            ->setParty($this->getParty($contact))
        ;
    }

    private function getParty(MerchantOrderContactInterface $contact): Party
    {
        $contactTitle = array_filter(
            [
                $contact->getFirstName(),
                $contact->getLastName(),
                (string)$contact->getCompany(),
            ],
            fn ($item) => $item !== ''
        );

        return (new Party())
            ->setTitle(implode(' ', $contactTitle))
            ->setEmail($contact->getEmail())
            ->setPhone($contact->getPhone())
        ;
    }
}
