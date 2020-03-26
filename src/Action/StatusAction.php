<?php

namespace IlCleme\Tinkl\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (! array_key_exists('status', $model)) {
            $request->markNew();

            return;
        }

        if (in_array($model['status'], ['pending', 'partial', 'deferred'])) {
            $request->markPending();

            return;
        }

        if ($model['status'] == 'payed') {
            $request->markPayedout();

            return;
        }

        if ($model['status'] == 'error') {
            $request->markFailed();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
