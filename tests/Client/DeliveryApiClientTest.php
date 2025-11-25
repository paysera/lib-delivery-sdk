<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Tests\Client;

use Exception;
use Paysera\Component\RestClientCommon\Exception\ClientException;
use Paysera\DeliveryApi\MerchantClient\Entity\Order;
use Paysera\DeliveryApi\MerchantClient\Entity\ProjectCredentials;
use Paysera\DeliverySdk\Client\DeliveryApiClient;
use Paysera\DeliverySdk\Client\DeliveryOrderApiClient;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Exception\CredentialsValidationException;
use Paysera\DeliverySdk\Exception\DeliveryOrderRequestException;
use Paysera\DeliverySdk\Exception\MerchantClientNotFoundException;
use Paysera\DeliverySdk\Exception\RateLimitExceededException;
use Paysera\DeliverySdk\Service\DeliveryLoggerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DeliveryApiClientTest extends TestCase
{
    private DeliveryOrderApiClient $orderRequestHandlerMock;
    private DeliveryLoggerInterface $loggerMock;
    private PayseraDeliveryOrderRequest $deliveryOrderRequestMock;
    private Order $orderMock;
    private DeliveryApiClient $deliveryApiClient;

    protected function setUp(): void
    {
        $this->orderRequestHandlerMock = $this->createMock(DeliveryOrderApiClient::class);
        $this->loggerMock = $this->createMock(DeliveryLoggerInterface::class);
        $this->deliveryOrderRequestMock = $this->createMock(PayseraDeliveryOrderRequest::class);
        $this->orderMock = $this->createMock(Order::class);

        $this->deliveryApiClient = new DeliveryApiClient(
            $this->orderRequestHandlerMock,
            $this->loggerMock
        );
    }

    public function testPostOrder(): void
    {
        $this->orderRequestHandlerMock->expects($this->once())
            ->method('create')
            ->with($this->deliveryOrderRequestMock)
            ->willReturn($this->orderMock)
        ;

        $result = $this->deliveryApiClient->postOrder($this->deliveryOrderRequestMock);

        $this->assertSame($this->orderMock, $result);
    }

    public function testPatchOrder(): void
    {
        $this->orderRequestHandlerMock->expects($this->once())
            ->method('update')
            ->with($this->deliveryOrderRequestMock)
            ->willReturn($this->orderMock)
        ;

        $result = $this->deliveryApiClient->patchOrder($this->deliveryOrderRequestMock);

        $this->assertSame($this->orderMock, $result);
    }

    public function testPrepaidOrder(): void
    {
        $this->orderRequestHandlerMock->expects($this->once())
            ->method('prepaid')
            ->with($this->deliveryOrderRequestMock)
            ->willReturn($this->orderMock)
        ;

        $result = $this->deliveryApiClient->prepaidOrder($this->deliveryOrderRequestMock);

        $this->assertSame($this->orderMock, $result);
    }

    public function testGetOrder(): void
    {
        $this->orderRequestHandlerMock->expects($this->once())
            ->method('get')
            ->with($this->deliveryOrderRequestMock)
            ->willReturn($this->orderMock)
        ;

        $result = $this->deliveryApiClient->getOrder($this->deliveryOrderRequestMock);

        $this->assertSame($this->orderMock, $result);
    }

    public function testPostOrderThrowsException(): void
    {
        $orderMock = $this->createMock(MerchantOrderInterface::class);
        $this->deliveryOrderRequestMock->method('getOrder')
            ->willReturn($orderMock)
        ;

        $orderMock->method('getNumber')->willReturn('12345');

        $this->orderRequestHandlerMock->method('create')
            ->willThrowException(new Exception('Error creating order'))
        ;

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                "Cannot perform operation 'create' on delivery order for order id 12345.",
                $this->isInstanceOf(Exception::class)
            )
        ;

        $this->expectException(DeliveryOrderRequestException::class);

        $this->deliveryApiClient->postOrder($this->deliveryOrderRequestMock);
    }

    public function testValidateCredentialsReturnsTrue(): void
    {
        $credentials = new ProjectCredentials([
            'project_id' => '123456',
            'password' => '6943a905f5a1a5ebd29b4f3c4c15b818',
        ]);

        $this->orderRequestHandlerMock->expects($this->once())
            ->method('validateCredentials')
            ->with($this->callback(function ($arg) use ($credentials) {
                return $arg instanceof ProjectCredentials
                    && $arg->getProjectId() === $credentials->getProjectId()
                    && $arg->getPassword() === $credentials->getPassword();
            }))
            ->willReturn(true);

        $result = $this->deliveryApiClient->validateCredentials($credentials);

        $this->assertTrue($result);
    }

    public function testValidateCredentialsReturnsFalse(): void
    {
        $credentials = new ProjectCredentials([
            'project_id' => '123456',
            'password' => 'invalid_password',
        ]);

        $this->orderRequestHandlerMock->expects($this->once())
            ->method('validateCredentials')
            ->with($this->callback(function ($arg) use ($credentials) {
                return $arg instanceof ProjectCredentials
                    && $arg->getProjectId() === $credentials->getProjectId()
                    && $arg->getPassword() === $credentials->getPassword();
            }))
            ->willReturn(false);

        $result = $this->deliveryApiClient->validateCredentials($credentials);

        $this->assertFalse($result);
    }

    public function testValidateCredentialsThrowsCredentialsValidationException(): void
    {
        $credentials = new ProjectCredentials([
            'project_id' => '123456',
            'password' => 'invalid_password',
        ]);

        $requestMock = $this->createMock(RequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(401);
        $responseMock->method('getReasonPhrase')->willReturn('Unauthorized');

        $clientException = new ClientException('Unauthorized', $requestMock, $responseMock);

        $this->orderRequestHandlerMock->method('validateCredentials')
            ->willThrowException($clientException);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('Credentials validation failed for project 123456: HTTP 401');

        $this->expectException(CredentialsValidationException::class);
        $this->expectExceptionMessage('401 Unauthorized');

        $this->deliveryApiClient->validateCredentials($credentials);
    }

    public function testValidateCredentialsThrowsRateLimitExceededException(): void
    {
        $credentials = new ProjectCredentials([
            'project_id' => '123456',
            'password' => '6943a905f5a1a5ebd29b4f3c4c15b818',
        ]);

        $requestMock = $this->createMock(RequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(429);

        $clientException = new ClientException('Too Many Requests', $requestMock, $responseMock);

        $this->orderRequestHandlerMock->method('validateCredentials')
            ->willThrowException($clientException);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('Credentials validation failed for project 123456: HTTP 429');

        $this->expectException(RateLimitExceededException::class);

        $this->deliveryApiClient->validateCredentials($credentials);
    }

    public function testValidateCredentialsThrowsMerchantClientNotFoundException(): void
    {
        $credentials = new ProjectCredentials([
            'project_id' => '123456',
            'password' => '6943a905f5a1a5ebd29b4f3c4c15b818',
        ]);

        $merchantClientException = new MerchantClientNotFoundException();

        $this->orderRequestHandlerMock->method('validateCredentials')
            ->willThrowException($merchantClientException);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('Credentials validation failed for project 123456: Merchant client not found');

        $this->expectException(MerchantClientNotFoundException::class);

        $this->deliveryApiClient->validateCredentials($credentials);
    }

    public function testValidateCredentialsHandlesMultipleHttpErrorCodes(): void
    {
        $credentials = new ProjectCredentials([
            'project_id' => '123456',
            'password' => 'test_password',
        ]);

        $requestMock = $this->createMock(RequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(403);
        $responseMock->method('getReasonPhrase')->willReturn('Forbidden');

        $clientException = new ClientException('Forbidden', $requestMock, $responseMock);

        $this->orderRequestHandlerMock->method('validateCredentials')
            ->willThrowException($clientException);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('Credentials validation failed for project 123456: HTTP 403');

        $this->expectException(CredentialsValidationException::class);
        $this->expectExceptionMessage('403 Forbidden');

        $this->deliveryApiClient->validateCredentials($credentials);
    }
}
