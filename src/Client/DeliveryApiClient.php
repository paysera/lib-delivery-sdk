<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Client;

use Closure;
use Exception;
use Paysera\Component\RestClientCommon\Exception\ClientException;
use Paysera\DeliveryApi\MerchantClient\Entity\Order;
use Paysera\DeliveryApi\MerchantClient\Entity\ProjectCredentials;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Exception\CredentialsValidationException;
use Paysera\DeliverySdk\Exception\DeliveryOrderRequestException;
use Paysera\DeliverySdk\Exception\MerchantClientNotFoundException;
use Paysera\DeliverySdk\Exception\RateLimitExceededException;
use Paysera\DeliverySdk\Service\DeliveryLoggerInterface;

class DeliveryApiClient
{
    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_PREPAID = 'prepaid';
    public const ACTION_GET = 'get';
    public const ACTION_VALIDATE_CREDENTIALS = 'validate_credentials';

    private DeliveryOrderApiClient $orderRequestHandler;
    private DeliveryLoggerInterface $logger;

    public function __construct(
        DeliveryOrderApiClient $orderRequestHandler,
        DeliveryLoggerInterface $logger
    ) {
        $this->orderRequestHandler = $orderRequestHandler;
        $this->logger = $logger;
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return Order
     * @throws DeliveryOrderRequestException
     */
    public function postOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        return $this->sendOrderRequest(
            self::ACTION_CREATE,
            fn (PayseraDeliveryOrderRequest $deliveryOrderRequest) => $this
                ->orderRequestHandler
                ->create($deliveryOrderRequest),
            $deliveryOrderRequest
        );
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return Order
     * @throws DeliveryOrderRequestException
     */
    public function patchOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        return $this->sendOrderRequest(
            self::ACTION_UPDATE,
            fn (PayseraDeliveryOrderRequest $deliveryOrderRequest) => $this
                ->orderRequestHandler
                ->update($deliveryOrderRequest),
            $deliveryOrderRequest
        );
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return Order
     * @throws DeliveryOrderRequestException
     */
    public function getOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        return $this->sendOrderRequest(
            self::ACTION_GET,
            fn (PayseraDeliveryOrderRequest $deliveryOrderRequest) => $this
                ->orderRequestHandler
                ->get($deliveryOrderRequest),
            $deliveryOrderRequest
        );
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return Order
     * @throws DeliveryOrderRequestException
     */
    public function prepaidOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        return $this->sendOrderRequest(
            self::ACTION_PREPAID,
            fn (PayseraDeliveryOrderRequest $deliveryOrderRequest) => $this->orderRequestHandler
                ->prepaid($deliveryOrderRequest),
            $deliveryOrderRequest
        );
    }

    /**
     * @param ProjectCredentials $credentials
     * @return bool
     * @throws CredentialsValidationException
     * @throws RateLimitExceededException
     * @throws MerchantClientNotFoundException
     */
    public function validateCredentials(ProjectCredentials $credentials): bool
    {
        try {
            return $this->orderRequestHandler->validateCredentials($credentials);
        } catch (MerchantClientNotFoundException $exception) {
            $this->logger->error(
                sprintf(
                    'Credentials validation failed for project %s: Merchant client not found',
                    $credentials->getProjectId()
                )
            );

            throw $exception;
        } catch (ClientException $exception) {
            $this->handleCredentialsValidationClientException($exception, $credentials);
        }
    }

    /**
     * @param string $action
     * @param Closure $handler
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return Order
     * @throws DeliveryOrderRequestException
     */
    private function sendOrderRequest(
        string $action,
        Closure $handler,
        PayseraDeliveryOrderRequest $deliveryOrderRequest
    ): Order {
        try {
            return $handler($deliveryOrderRequest);
        } catch (Exception $exception) {
            $this->logException($action, $exception, $deliveryOrderRequest->getOrder());

            throw new DeliveryOrderRequestException($exception);
        }
    }

    private function logException(string $action, Exception $exception, MerchantOrderInterface $order): void
    {
        $this->logger->error(
            sprintf(
                'Cannot perform operation \'%s\' on delivery order for order id %s.',
                $action,
                $order->getNumber(),
            ),
            $exception
        );
    }

    /**
     * @param ClientException $exception
     * @param ProjectCredentials $credentials
     * @return void
     * @throws RateLimitExceededException
     * @throws CredentialsValidationException
     */
    private function handleCredentialsValidationClientException(
        ClientException $exception,
        ProjectCredentials $credentials
    ): void {
        $response = $exception->getResponse();

        $this->logger->error(
            sprintf(
                'Credentials validation failed for project %s: HTTP %d',
                $credentials->getProjectId(),
                $response->getStatusCode()
            )
        );

        if ($response->getStatusCode() === 429) {
            throw new RateLimitExceededException(
                $exception
            );
        }

        throw new CredentialsValidationException(
            sprintf(
                '%s %s',
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ),
            $exception
        );
    }
}
