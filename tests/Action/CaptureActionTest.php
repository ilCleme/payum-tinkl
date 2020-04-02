<?php

namespace IlCleme\Tinkl\Tests\Action;

use IlCleme\Tinkl\Action\CaptureAction;
use IlCleme\Tinkl\Request\CreateInvoice;
use IlCleme\Tinkl\Request\StatusInvoice;
use Payum\Core\Exception\LogicException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Generic;
use Payum\Core\Storage\IdentityInterface;
use Payum\Core\Tests\GenericActionTest;

class CaptureActionTest extends GenericActionTest
{
    protected $requestClass = Capture::class;

    protected $actionClass = CaptureAction::class;

    public function provideNotSupportedRequests()
    {
        return [
            ['foo'],
            [['foo']],
            [new \stdClass()],
            [new $this->requestClass('foo')],
            [new $this->requestClass(new \stdClass())],
            [$this->getMockForAbstractClass(Generic::class, [[]])],
            [$this->getMockForAbstractClass(IdentityInterface::class)],
        ];
    }

    /**
     * @test
     */
    public function testCaptureNewRequest()
    {
        [$tokenMock, $genericTokenFactory, $gatewayMock] = $this->getMockForCapture($this->returnCallback(function (CreateInvoice $request) {
            $model = $request->getModel();
            $model['status'] = 'pending';
            $model['url'] = 'aUrl';
            $request->setModel($model);
        }));

        $request = new Capture($tokenMock);
        $request->setModel([]);
        $action = new CaptureAction();
        $action->setGateway($gatewayMock);
        $action->setGenericTokenFactory($genericTokenFactory);
        try {
            $action->execute($request);
        } catch (HttpRedirect $httpRedirect) {
            $this->assertInstanceOf(HttpRedirect::class, $httpRedirect);
            $this->assertArrayHasKey('status', $request->getModel());
            $this->assertEquals('aUrl', $httpRedirect->getUrl());
            $this->assertArrayHasKey('notification_url', $request->getModel());
            $this->assertArrayHasKey('redirect_url', $request->getModel());
            $this->assertArrayHasKey('url', $request->getModel());
        }
    }

    /**
     * @test
     */
    public function testCaptureNewRequestDeferred()
    {
        [$tokenMock, $genericTokenFactory, $gatewayMock] = $this->getMockForCapture($this->returnCallback(function (CreateInvoice $request) {
            $model = $request->getModel();
            $model['status'] = 'deferred';
            $model['url'] = 'captureUrl';
            $model['activation_page'] = 'activationUrl';
            $request->setModel($model);
        }));

        $request = new Capture($tokenMock);
        $request->setModel([]);
        $action = new CaptureAction();
        $action->setGateway($gatewayMock);
        $action->setGenericTokenFactory($genericTokenFactory);
        try {
            $action->execute($request);
        } catch (HttpRedirect $httpRedirect) {
            $this->assertInstanceOf(HttpRedirect::class, $httpRedirect);
            $this->assertArrayHasKey('status', $request->getModel());
            $this->assertEquals('activationUrl', $httpRedirect->getUrl());
            $this->assertArrayHasKey('notification_url', $request->getModel());
            $this->assertArrayHasKey('redirect_url', $request->getModel());
            $this->assertArrayHasKey('url', $request->getModel());
        }
    }

    /**
     * @test
     */
    public function testCapturePendingRequest()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(StatusInvoice::class))
            ->will($this->returnCallback(function (StatusInvoice $request) {
                $model = $request->getModel();
                $this->assertEquals('pending', $model['status']);
                $model['status'] = 'pending';
                $model['invoice'] = 'fooData';
                $request->setModel($model);
            }));

        $request = new Capture(['status' => 'pending']);
        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute($request);
        $this->assertArrayHasKey('status', $request->getModel());
        $this->assertArrayHasKey('invoice', $request->getModel());
        $this->assertEquals('fooData', $request->getModel()['invoice']);
    }

    /**
     * @test
     */
    public function testCaptureDeferredRequest()
    {
        $gatewayMock = $this->createGatewayMock();
        $request = new Capture(['status' => 'deferred', 'activation_page' => 'activationPageUrl']);
        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        try {
            $action->execute($request);
        } catch (HttpRedirect $exception) {
            $this->assertEquals('activationPageUrl', $exception->getUrl());
            $this->assertArrayHasKey('status', $request->getModel());
            $this->assertEquals('deferred', $request->getModel()['status']);
        }
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('IlCleme\Tinkl\Api');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createGenericTokenFactoryMock()
    {
        return $this->createMock('\Payum\Core\Security\GenericTokenFactoryInterface');
    }

    protected function getMockForCapture($returnResponseGatewayMock)
    {
        $tokenMock = $this->createTokenMock();
        $tokenMock
            ->expects($this->once())
            ->method('getGatewayName')
            ->willReturn('gatewayName');

        $identityMock = $this->getMockClass(IdentityInterface::class);
        $tokenMock
            ->expects($this->once())
            ->method('getDetails')
            ->willReturn($identityMock);

        $tokenMock
            ->expects($this->exactly(2))
            ->method('getTargetUrl')
            ->willReturn('notifyUrl', 'captureUrl');

        $genericTokenFactory = $this->createGenericTokenFactoryMock();
        $genericTokenFactory
            ->expects($this->once())
            ->method('createNotifyToken')
            ->with(
                'gatewayName',
                $this->identicalTo($identityMock)
            )->willReturn($tokenMock);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CreateInvoice::class))
            ->will($returnResponseGatewayMock);

        return [$tokenMock, $genericTokenFactory, $gatewayMock];
    }
}
