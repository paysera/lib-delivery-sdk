<?php

declare(strict_types=1);

namespace Paysera\DeliverySdk\Client\Provider;

use Exception;
use Paysera\DeliveryApi\MerchantClient\ClientFactory;
use Paysera\DeliveryApi\MerchantClient\MerchantClient;
use Paysera\DeliverySdk\Entity\PayseraDeliverySettingsInterface;
use Paysera\DeliverySdk\Exception\MerchantClientNotFoundException;
use Paysera\DeliverySdk\Service\DeliveryLoggerInterface;

class MerchantClientProvider
{
    private const DEFAULT_BASE_URL = 'https://delivery-api.paysera.com/rest/v1/';

    private DeliveryLoggerInterface $logger;

    public function __construct(DeliveryLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getMerchantClient(PayseraDeliverySettingsInterface $deliverySettings): MerchantClient
    {
        $macId = $deliverySettings->getProjectId();
        $macSecret = $deliverySettings->getProjectPassword();

        if ($macId === null || $macSecret === null) {
            throw new MerchantClientNotFoundException();
        }

        $clientFactory = new ClientFactory([
            'base_url' => $this->getBaseUrl(),
            'mac' => [
                'mac_id' => $macId,
                'mac_secret' => $macSecret,
            ],
        ]);

        try {
            $merchantClient = $clientFactory->getMerchantClient();
        } catch (Exception $exception) {
            $this->logger->error('Cannot create merchant client', $exception);

            throw new MerchantClientNotFoundException();
        }

        return $merchantClient;
    }

    private function getBaseUrl(): string
    {
        $url = getenv('DELIVERY_API_URL');

        return !empty($url) ? $url : self::DEFAULT_BASE_URL;
    }
}