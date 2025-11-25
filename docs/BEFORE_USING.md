## Integration with SDK

This SDK provides tools for working with the Paysera Delivery API. To ensure the proper functioning of the services in this SDK, you need to implement a set of interfaces in your system:

1. [MerchantOrderInterface.php](../src/Entity/MerchantOrderInterface.php) - An interface for accessing your (merchant) order data.
   - [MerchantOrderPartyInterface.php](../src/Entity/MerchantOrderPartyInterface.php) - Recipient information. Since there are cases where the recipient and the payer are different people, two separate implementations are required for this. In `MerchantOrderInterface`, recipient data is retrieved through `getShipping`, and payer data through `getBilling`.
     + [MerchantOrderContactInterface.php](../src/Entity/MerchantOrderContactInterface.php) - Information about the contact person
     + [MerchantOrderAddressInterface.php](../src/Entity/MerchantOrderAddressInterface.php) - Contact person's address
     + [DeliveryTerminalLocationInterface.php](../src/Entity/DeliveryTerminalLocationInterface.php) - Information about the contact person's delivery terminal location
   - [MerchantOrderItemInterface.php](../src/Entity/MerchantOrderItemInterface.php) - An interface for accessing order's item data.
   - [NotificationCallbackInterface.php](../src/Entity/NotificationCallbackInterface.php) - Configuration of the URL address for receiving callbacks and the events that the callbacks will be sent for (the SDK currently only supports handling the order_updated event).
   - [PayseraDeliveryGatewayInterface.php](../src/Entity/PayseraDeliveryGatewayInterface.php) - Information about the delivery gateway (only those provided by Paysera Delivery API)
     + [PayseraDeliveryGatewaySettingsInterface.php](../src/Entity/PayseraDeliveryGatewaySettingsInterface.php) - Gateway settings
2. [PayseraDeliverySettingsInterface.php](../src/Entity/PayseraDeliverySettingsInterface.php) - The plugin settings
3. [MerchantOrderRepositoryInterface.php](../src/Repository/MerchantOrderRepositoryInterface.php) - An interface for your (merchant) orders storage
4. [DeliveryGatewayRepositoryInterface.php](../src/Repository/DeliveryGatewayRepositoryInterface.php) - An interface for plugin's delivery gateways storage (should provide only Paysera Delivery API gateways)
5. [DeliveryLoggerInterface.php](../src/Service/DeliveryLoggerInterface.php) - A logger for info and error messages related to Delivery Order processing
6. [MerchantOrderLoggerInterface.php](../src/Service/MerchantOrderLoggerInterface.php) - A logger for info messages related to your (merchant) order processing

## And after implementing necessary interfaces, go to use cases
- [Validating credentials](VALIDATING_CREDENTIALS.md)
- [Delivery orders creating](DELIVERY_ORDER_CREATING.md)
- [Delivery orders updating](DELIVERY_ORDER_UPDATING.md)
- [Callbacks handling](HANDLING_CALLBACKS_FROM_API.md)
