# Payum Tinkl Gateway

This payum extension provides Tinkl payment integration. Tinkl is a gateway that provide ability to pay in bitcoin. 

1. Install gateway

```bash
$ composer require ilcleme/payum-tinkl
```
The gateway need to be configured to be includes in payum gateways.
To add the the gateway on payum you can use the instructions above.

```php
<?php
//config.php
include_once "vendor/autoload.php";

use Payum\Core\PayumBuilder;
use Payum\Core\Payum;
use Payum\Core\Model\Payment;

$paymentClass = Payment::class;

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGatewayFactory('tinkl', function ($config, $coreGatewayFactory){
        return new \IlCleme\Tinkl\TinklGatewayFactory($config, $coreGatewayFactory);
    })
    ->addGateway('tinkl', [
        'factory' => 'tinkl',
        'clientId' => 'aClientId',
        'token' => 'aToken',
        'sandobx' => 'true', // switch to false to use in production environment
    ])
    ->getPayum();

```

## Test
To run the package test you need to install the dev requirements (test tools) and run phpunit from the package folder
```
composer install --dev
vendor/bin/phpunit tests
```
if you want a more verbose log use this command
```
vendor/bin/phpunit --testdox tests
```

## License

This extension is released under the [MIT License](LICENSE).
