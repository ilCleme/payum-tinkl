<?php
namespace IlCleme\Tinkl\Action\Api;

use IlCleme\Tinkl\Request\CreateInvoice;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Bridge\Spl\ArrayObject;

class CreateInvoiceAction extends BaseApiAwareAction
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

        $details->replace((array) $this->api->createInvoice((array) $details));

        $request->setModel($details);
    }

    /**
     * @param mixed $request
     *
     * @return boolean
     */
    public function supports($request)
    {
        return
            $request instanceof CreateInvoice &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
