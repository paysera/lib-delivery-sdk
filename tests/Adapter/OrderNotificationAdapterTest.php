<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\phpunit\Adapter;

use Paysera\DeliveryApi\MerchantClient\Entity\OrderNotificationCreate;
use Paysera\DeliverySdk\Adapter\OrderNotificationAdapter;
use Paysera\DeliverySdk\Entity\NotificationCallbackInterface;
use PHPUnit\Framework\TestCase;

class OrderNotificationAdapterTest extends TestCase
{
    private OrderNotificationAdapter $adapter;
    private NotificationCallbackInterface $callbackDtoMock;

    protected function setUp(): void
    {
        $this->adapter = new OrderNotificationAdapter();
        $this->callbackDtoMock = $this->createMock(NotificationCallbackInterface::class);
    }

    public function testConvert(): void
    {
        $url = 'https://example.com/notification';
        $events = ['event1', 'event2'];

        $this->callbackDtoMock->method('getUrl')->willReturn($url);
        $this->callbackDtoMock->method('getEvents')->willReturn($events);

        $orderNotification = $this->adapter->convert($this->callbackDtoMock);

        $this->assertInstanceOf(OrderNotificationCreate::class, $orderNotification);
        $this->assertSame($url, $orderNotification->getUrl());
        $this->assertSame($events, $orderNotification->getEvents());
    }
}
