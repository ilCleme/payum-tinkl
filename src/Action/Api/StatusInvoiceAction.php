<?php

namespace IlCleme\Tinkl\Action\Api;

use IlCleme\Tinkl\Request\StatusInvoice;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

class StatusInvoiceAction extends BaseApiAwareAction
{
    /**
     * @param mixed $request
     *
     * @throws \Payum\Core\Exception\RequestNotSupportedException if the action dose not support the request.
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $details->replace((array) $this->api->getStatusInvoice((array) $details));

        $request->setModel($details);
    }

    /**
     * @param mixed $request
     *
     * @return bool
     */
    public function supports($request)
    {
        return
            $request instanceof StatusInvoice &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
