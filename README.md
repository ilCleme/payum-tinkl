# Payum Tinkl Gateway

This payum gateway provides the functionality of receiving bitcoin payments for all your web applications. 
Take advantage of the [Tinkl](https://www.tinkl.it) service, which provides an API to manage the payment in Bitcoin.

Requirements
------------
The minimum requirements of the package are PHP 7.1 installed in your webserver and Payum, which will be installed directly with the gateway.

Installation
------------
To install package you can use a simple composer command.
```bash
$ composer require ilcleme/payum-tinkl
```
Configuration
------------
Once installed via composer, it must be configured and added to the gateways in Payum. 
To do this, simply follow these steps: 
- Register the gateway in Payum;
- Configure your credentials (you can also enable the sandbox environment);
- Configure any additional options (not mandatory)

The following PHP script is an example of how the gateway can be configured, you may need to modify it to configure it in your web application.
If you have never used Payum or you don't know its rules, I recommend you read the documentation available on [Github](https://github.com/Payum/Payum/blob/master/docs/index.md).
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
        'sandbox' => true, // switch to false to use in production environment
    ])
    ->getPayum();

```
The expiry time of the Tinkl payment page can also be configured, simply enter it in the payment details, in this way:
```php
<?php
// prepare.php
include __DIR__.'/config.php';
$gatewayName = 'tinkl';

/** @var \Payum\Core\Payum $payum */
$storage = $payum->getStorage($paymentClass);

$payment = $storage->create();
$payment->setNumber(uniqid());
$payment->setCurrencyCode('EUR');
$payment->setTotalAmount(0.5);
$payment->setDescription('A description');
$payment->setClientId('anId');
$payment->setClientEmail('foo@example.com');
$payment->setDetails([
    'time_limit' => 60 // Value accepted in range from 60 to 900 (1 to 15 minutes)
]);

$storage->update($payment);
$captureToken = $payum->getTokenFactory()->createCaptureToken($gatewayName, $payment, 'done.php');
header("Location: ".$captureToken->getTargetUrl());
```

## Test
To run the package test you need to install the dev requirements (test tools) and run phpunit from the package folder
```
composer install --dev
vendor/bin/phpunit tests
```

## License

This extension is released under the [MIT License](LICENSE).
