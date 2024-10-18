<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\phpunit\Adapter;

use Paysera\DeliveryApi\MerchantClient\Entity\Contact;
use Paysera\DeliveryApi\MerchantClient\Entity\Party;
use Paysera\DeliverySdk\Adapter\ContactAdapter;
use Paysera\DeliverySdk\Entity\MerchantOrderContactInterface;
use PHPUnit\Framework\TestCase;

class ContactAdapterTest extends TestCase
{
    private ContactAdapter $contactAdapter;
    private MerchantOrderContactInterface $contactMock;

    protected function setUp(): void
    {
        $this->contactAdapter = new ContactAdapter();
        $this->contactMock = $this->createMock(MerchantOrderContactInterface::class);
    }

    /**
     * @dataProvider contactDataProvider
     */
    public function testConvert(array $contactData, array $expectedPartyData): void
    {
        foreach ($contactData as $method => $returnValue) {
            $this->contactMock->method($method)->willReturn($returnValue);
        }

        $contact = $this->contactAdapter->convert($this->contactMock);
        $this->assertInstanceOf(Contact::class, $contact);

        $party = $contact->getParty();
        $this->assertInstanceOf(Party::class, $party);
        $this->assertSame($expectedPartyData['title'], $party->getTitle());
        $this->assertSame($expectedPartyData['email'], $party->getEmail());
        $this->assertSame($expectedPartyData['phone'], $party->getPhone());
    }

    public function contactDataProvider(): iterable
    {
        yield 'full contact info' => [
            'contactData' => [
                'getFirstName' => 'John',
                'getLastName' => 'Doe',
                'getCompany' => 'Acme Corp',
                'getEmail' => 'john.doe@example.com',
                'getPhone' => '+1234567890',
            ],
            'expectedPartyData' => [
                'title' => 'John Doe Acme Corp',
                'email' => 'john.doe@example.com',
                'phone' => '+1234567890',
            ],
        ];

        yield 'contact without company' => [
            'contactData' => [
                'getFirstName' => 'Jane',
                'getLastName' => 'Smith',
                'getCompany' => '',
                'getEmail' => 'jane.smith@example.com',
                'getPhone' => '+0987654321',
            ],
            'expectedPartyData' => [
                'title' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+0987654321',
            ],
        ];

        yield 'contact without last name' => [
            'contactData' => [
                'getFirstName' => 'Alex',
                'getLastName' => '',
                'getCompany' => 'Innovate Ltd',
                'getEmail' => 'alex@innovate.com',
                'getPhone' => '+1122334455',
            ],
            'expectedPartyData' => [
                'title' => 'Alex Innovate Ltd',
                'email' => 'alex@innovate.com',
                'phone' => '+1122334455',
            ],
        ];
    }
}
