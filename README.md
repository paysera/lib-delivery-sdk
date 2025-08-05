# Delivery SDK
PHP SDK for Paysera delivery gateway integration

## Code style
[PSR-12](https://www.php-fig.org/psr/psr-12)

## Used
<b>Built with</b>
- [lib-delivery-api-merchant-client](https://github.com/paysera/lib-delivery-api-merchant-client)

## Installation (will be updated soon)
While in under developing:
```
{
    "require": {
        "php": ">=7.4",
        "paysera/lib-delivery-sdk": "^0.2",
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/paysera/lib-delivery-sdk"
        }
    ]
}
```

Then, implement all necessary interfaces:

- [Integration with SDK](docs/BEFORE_USING.md)

## Use cases
- [Delivery orders creating](docs/DELIVERY_ORDER_CREATING.md)
- [Delivery orders updating](docs/DELIVERY_ORDER_UPDATING.md)
- [Callbacks handling](docs/HANDLING_CALLBACKS_FROM_API.md)


## Run tests
```
$ bash run_tests.sh
```
