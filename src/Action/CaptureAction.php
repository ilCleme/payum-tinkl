<?php

namespace IlCleme\Tinkl\Action;

use IlCleme\Tinkl\Request\ActivateInvoice;
use IlCleme\Tinkl\Request\CreateInvoice;
use IlCleme\Tinkl\Request\StatusInvoice;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Storage\IdentityInterface;

class CaptureAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait, GenericTokenFactoryAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (! $model->get('status', false)) {
            $this->captureNewRequest($request, $model);
        }

        if ($model->get('status') == 'deferred') {
            $this->captureDeferredRequest($request, $model);

            return;
        }

        if ($model->get('status') == 'pending') {
            $this->capturePendingRequest($request, $model);

            return;
        }
    }

    /**
     * Execute required operation for new request.
     *
     * @param Capture $request
     * @param ArrayObject $model
     */
    protected function captureNewRequest($request, $model)
    {
        $notifyToken = $this->tokenFactory->createNotifyToken(
            $request->getToken()->getGatewayName(),
            $request->getToken()->getDetails()
        );

        $model['notification_url'] = $notifyToken->getTargetUrl();
        $model['redirect_url'] = $request->getToken()->getTargetUrl();

        $this->gateway->execute($invoice = new CreateInvoice($model));

        $request->setModel($model = $invoice->getModel());

        if ($model['url'] && $model['status'] == 'pending') {
            throw new HttpRedirect($model['url']);
        } else {
            throw new HttpRedirect($model['redirect_url']);
        }
    }

    /**
     * Execute operation for pending request.
     *
     * @param Capture $request
     * @param ArrayObject $model
     */
    protected function capturePendingRequest($request, $model)
    {
        $this->gateway->execute($invoice = new StatusInvoice($model));

        /** @var ArrayObject $model */
        $model = $invoice->getModel();
        $request->setModel($model);
    }

    /**
     * Execute operation for pending request.
     *
     * @param Capture $request
     * @param ArrayObject $model
     */
    protected function captureDeferredRequest($request, $model)
    {
        $getHttpRequest = new GetHttpRequest();
        $this->gateway->execute($getHttpRequest);
        if ($getHttpRequest->method == 'POST' && isset($getHttpRequest->request['activateIntent'])) {
            $this->gateway->execute($activateInvoice = new ActivateInvoice($model));
            $request->setModel($model = $activateInvoice->getModel());

            throw new HttpRedirect($model['url']);
        }

        $this->gateway->execute($renderTemplate = new RenderTemplate('@PayumTinkl/Action/activate_deferred.html.twig', [
            'model' => $model,
        ]));

        throw new HttpResponse($renderTemplate->getResult());
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            ($request->getModel() instanceof \ArrayAccess ||
            $request->getModel() instanceof IdentityInterface);
    }
}
