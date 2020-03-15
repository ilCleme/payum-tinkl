# Payum Tinkl Gateway

Queso gateway di payum aggiunge la possibilità a chi lo utilizza di ricevere pagamenti in bitcoin sfruttando le API di Tinkl
Tinkl è un servizio che fornisce un API e permette di ricevere un pagamento in bitcoine convertirlo immediatamente in valuta FIAT.

1. Installa il gateway

```bash
$ composer require ilcleme/payum-tinkl
```
L'estensione dipende strettamente da Payum, quindi una volta installato tramite composer va configurato per essere usufruito da payum.
Per aggiungerlo ai gateway di payum è necessario segiure questi passi:

1. Aggiunta gateway a payum

```php
<?php

//SCRIPT PER GESTIRE AGGIUNTA DEL GATEWAY
```

In questo momento il gateway è aggiuntoa payum, per testare la corretta gestione del gateway basta fare così:

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
