<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\Client\Provider;

use Paysera\DeliverySdk\Client\Provider\MerchantClientProvider;
use Paysera\DeliverySdk\Entity\PayseraDeliverySettingsInterface;
use Paysera\DeliverySdk\Exception\MerchantClientNotFoundException;
use Paysera\DeliverySdk\Service\DeliveryLoggerInterface;
use Paysera\DeliveryApi\MerchantClient\MerchantClient;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class MerchantClientProviderTest extends TestCase
{
    private DeliveryLoggerInterface $logger;
    private PayseraDeliverySettingsInterface $deliverySettings;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(DeliveryLoggerInterface::class);
        $this->deliverySettings = $this->createMock(PayseraDeliverySettingsInterface::class);
    }

    public function testReturnsMerchantClientSuccessfully(): void
    {
        $this->deliverySettings->method('getProjectId')->willReturn(123);
        $this->deliverySettings->method('getProjectPassword')->willReturn('secret');
        $this->deliverySettings->method('getUserAgent')->willReturn('TestAgent');
        $this->deliverySettings->method('isTestModeEnabled')->willReturn(false);

        $provider = new MerchantClientProvider($this->logger);
        $client = $provider->getMerchantClient($this->deliverySettings);

        $this->assertInstanceOf(MerchantClient::class, $client);
    }

    public function testThrowsExceptionWhenCredentialsAreMissing(): void
    {
        $this->expectException(MerchantClientNotFoundException::class);

        $this->deliverySettings->method('getProjectId')->willReturn(null);
        $this->deliverySettings->method('getProjectPassword')->willReturn(null);
        $this->deliverySettings->method('getUserAgent')->willReturn('TestAgent');
        $this->deliverySettings->method('isTestModeEnabled')->willReturn(false);

        $provider = new MerchantClientProvider($this->logger);
        $provider->getMerchantClient($this->deliverySettings);
    }

    public function testCustomHeadersAreSetCorrectly(): void
    {
        $this->deliverySettings->method('getProjectId')->willReturn(123);
        $this->deliverySettings->method('getProjectPassword')->willReturn('topsecret');
        $this->deliverySettings->method('getUserAgent')->willReturn('MyTestAgent');
        $this->deliverySettings->method('isTestModeEnabled')->willReturn(true);

        $provider = new MerchantClientProvider($this->logger);
        $client = $provider->getMerchantClient($this->deliverySettings);

        $apiClient = $this->getPrivateProperty($client, 'apiClient');
        $options = $this->getPrivateProperty($apiClient, 'options');

        $this->assertArrayHasKey('headers', $options);
        $this->assertSame('MyTestAgent', $options['headers']['User-Agent']);
        $this->assertTrue($options['headers']['Paysera-Test-Mode']);
    }

    public function testReturnsPublicMerchantClientSuccessfully(): void
    {
        $provider = new MerchantClientProvider($this->logger);
        $client = $provider->getPublicMerchantClient();

        $this->assertInstanceOf(MerchantClient::class, $client);
    }

    public function testPublicMerchantClientHasNoMacAuthentication(): void
    {
        $provider = new MerchantClientProvider($this->logger);
        $client = $provider->getPublicMerchantClient();

        $apiClient = $this->getPrivateProperty($client, 'apiClient');
        $options = $this->getPrivateProperty($apiClient, 'options');

        $this->assertArrayNotHasKey('mac', $options);
    }

    public function testPublicMerchantClientHasCorrectUserAgent(): void
    {
        $provider = new MerchantClientProvider($this->logger);
        $client = $provider->getPublicMerchantClient();

        $apiClient = $this->getPrivateProperty($client, 'apiClient');
        $options = $this->getPrivateProperty($apiClient, 'options');

        $this->assertArrayHasKey('headers', $options);
        $this->assertSame('Paysera-Delivery-SDK', $options['headers']['User-Agent']);
    }

    private function getPrivateProperty(object $object, string $propertyName)
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}
