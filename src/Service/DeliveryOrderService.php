<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Service;

use Paysera\DeliveryApi\MerchantClient\Entity\Order;
use Paysera\DeliveryApi\MerchantClient\Entity\ProjectCredentials;
use Paysera\DeliverySdk\Client\DeliveryApiClient;
use Paysera\DeliverySdk\Entity\MerchantOrderInterface;
use Paysera\DeliverySdk\Entity\PayseraDeliveryOrderRequest;
use Paysera\DeliverySdk\Exception\CredentialsValidationException;
use Paysera\DeliverySdk\Exception\DeliveryOrderRequestException;
use Paysera\DeliverySdk\Exception\MerchantClientNotFoundException;
use Paysera\DeliverySdk\Exception\RateLimitExceededException;
use Paysera\DeliverySdk\Repository\MerchantOrderRepositoryInterface;

class DeliveryOrderService
{
    private const LOG_MESSAGE_STARTED = 'Attempting to perform operation \'%s\' of delivery order for order id %s with project id: %s';
    private const LOG_MESSAGE_COMPLETED = 'Operation \'%s\' of delivery order %s for order id %d is completed.';

    private MerchantOrderRepositoryInterface $merchantOrderRepository;
    private DeliveryApiClient $deliveryApiClient;
    private DeliveryLoggerInterface $logger;

    public function __construct(
        MerchantOrderRepositoryInterface $merchantOrderRepository,
        DeliveryApiClient $deliveryApiClient,
        DeliveryLoggerInterface $logger
    ) {
        $this->deliveryApiClient = $deliveryApiClient;
        $this->logger = $logger;
        $this->merchantOrderRepository = $merchantOrderRepository;
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return MerchantOrderInterface|null
     * @throws DeliveryOrderRequestException
     */
    public function createDeliveryOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): ?MerchantOrderInterface
    {
        $this->logStepStarted(DeliveryApiClient::ACTION_CREATE, $deliveryOrderRequest);
        $deliveryOrder = $this->handleCreating($deliveryOrderRequest);
        $this->logStepCompleted(DeliveryApiClient::ACTION_CREATE, $deliveryOrderRequest, $deliveryOrder);

        return $deliveryOrderRequest->getOrder();
    }

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return MerchantOrderInterface|null
     * @throws DeliveryOrderRequestException
     */
    public function updateDeliveryOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): ?MerchantOrderInterface
    {
        $this->logStepStarted(DeliveryApiClient::ACTION_UPDATE, $deliveryOrderRequest);
        $deliveryOrder = $this->deliveryApiClient->patchOrder($deliveryOrderRequest);
        $this->logStepCompleted(DeliveryApiClient::ACTION_UPDATE, $deliveryOrderRequest, $deliveryOrder);

        return $deliveryOrderRequest->getOrder();
    }

    /**
     * @throws DeliveryOrderRequestException
     */
    public function prepaidDeliveryOrder(PayseraDeliveryOrderRequest $deliveryOrderRequest): ?MerchantOrderInterface
    {
        $this->logStepStarted(DeliveryApiClient::ACTION_PREPAID, $deliveryOrderRequest);
        $deliveryOrder = $this->deliveryApiClient->prepaidOrder($deliveryOrderRequest);
        $this->logStepCompleted(DeliveryApiClient::ACTION_PREPAID, $deliveryOrderRequest, $deliveryOrder);
        return $deliveryOrderRequest->getOrder();
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
        $this->logCredentialsValidationStarted($credentials);

        try {
            $isValid = $this->deliveryApiClient->validateCredentials($credentials);
            $this->logCredentialsValidationCompleted($credentials, $isValid);

            return $isValid;
        } catch (RateLimitExceededException $exception) {
            $this->logCredentialsValidationRateLimitError($credentials, $exception);

            throw $exception;
        } catch (MerchantClientNotFoundException $exception) {
            $this->logCredentialsValidationMerchantNotFoundError($credentials, $exception);

            throw $exception;
        } catch (CredentialsValidationException $exception) {
            $this->logCredentialsValidationError($credentials, $exception);

            throw $exception;
        }
    }

    #region Handling

    /**
     * @param PayseraDeliveryOrderRequest $deliveryOrderRequest
     * @return Order
     * @throws DeliveryOrderRequestException
     */
    private function handleCreating(PayseraDeliveryOrderRequest $deliveryOrderRequest): Order
    {
        $order = $deliveryOrderRequest->getOrder();
        $deliveryOrder = $this->deliveryApiClient->postOrder($deliveryOrderRequest);

        $order->setDeliveryOrderId($deliveryOrder->getId());
        $order->setDeliveryOrderNumber($deliveryOrder->getNumber());
        $this->merchantOrderRepository->save($order);

        return $deliveryOrder;
    }

    #endregion

    #region Service Methods

    private function logStepStarted(string $action, PayseraDeliveryOrderRequest $request): void
    {
        $this->logger->info(
            sprintf(
                self::LOG_MESSAGE_STARTED,
                $action,
                $request->getOrder()->getNumber(),
                $request->getDeliverySettings()->getProjectId()
            )
        );
    }

    private function logStepCompleted(
        string $action,
        PayseraDeliveryOrderRequest $deliveryOrderRequest,
        Order $deliveryOrder
    ): void {
        $order = $deliveryOrderRequest->getOrder();
        $orderNumber = $deliveryOrder->getNumber();

        $this->logger->info(
            sprintf(
                self::LOG_MESSAGE_COMPLETED,
                $action,
                $orderNumber,
                $order->getNumber(),
            )
        );
    }

    private function logCredentialsValidationStarted(ProjectCredentials $credentials): void
    {
        $this->logger->info(
            sprintf(
                'Attempting to perform operation \'%s\' for project id: %s',
                DeliveryApiClient::ACTION_VALIDATE_CREDENTIALS,
                $credentials->getProjectId()
            )
        );
    }

    private function logCredentialsValidationCompleted(ProjectCredentials $credentials, bool $isValid): void
    {
        $this->logger->info(
            sprintf(
                'Operation \'%s\' for project id %s completed with result: %s',
                DeliveryApiClient::ACTION_VALIDATE_CREDENTIALS,
                $credentials->getProjectId(),
                $isValid ? 'valid' : 'invalid'
            )
        );
    }

    private function logCredentialsValidationRateLimitError(
        ProjectCredentials $credentials,
        RateLimitExceededException $exception
    ): void {
        $this->logger->error(
            sprintf(
                'Operation \'%s\' for project id %s failed due to rate limit.',
                DeliveryApiClient::ACTION_VALIDATE_CREDENTIALS,
                $credentials->getProjectId()
            ),
            $exception
        );
    }

    private function logCredentialsValidationMerchantNotFoundError(
        ProjectCredentials $credentials,
        MerchantClientNotFoundException $exception
    ): void {
        $this->logger->error(
            sprintf(
                'Operation \'%s\' for project id %s failed: merchant client not found.',
                DeliveryApiClient::ACTION_VALIDATE_CREDENTIALS,
                $credentials->getProjectId()
            ),
            $exception
        );
    }

    private function logCredentialsValidationError(
        ProjectCredentials $credentials,
        CredentialsValidationException $exception
    ): void {
        $this->logger->error(
            sprintf(
                'Operation \'%s\' for project id %s failed.',
                DeliveryApiClient::ACTION_VALIDATE_CREDENTIALS,
                $credentials->getProjectId()
            ),
            $exception
        );
    }

    #endregion
}
