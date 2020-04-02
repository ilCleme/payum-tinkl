<?php

namespace IlCleme\Tinkl;

use IlCleme\Tinkl\Action\Api\ActivateInvoiceAction;
use IlCleme\Tinkl\Action\Api\CreateInvoiceAction;
use IlCleme\Tinkl\Action\Api\StatusInvoiceAction;
use IlCleme\Tinkl\Action\AuthorizeAction;
use IlCleme\Tinkl\Action\CaptureAction;
use IlCleme\Tinkl\Action\ConvertPaymentAction;
use IlCleme\Tinkl\Action\NotifyAction;
use IlCleme\Tinkl\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class TinklGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritdoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'tinkl',
            'payum.factory_title' => 'Tinkl',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.authorize' => new AuthorizeAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.api.create_invoice' => new CreateInvoiceAction(),
            'payum.action.api.status_invoice' => new StatusInvoiceAction(),
            'payum.action.api.activate_invoice' => new ActivateInvoiceAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'clientId' => null,
                'token' => null,
                'sandbox' => true,
                'deferred' => false,
                'version' => 'v1',
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['clientId', 'token'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
