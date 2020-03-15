<?php
namespace IlCleme\Tinkl\Tests\Action;

use IlCleme\Tinkl\Action\CaptureAction;
use IlCleme\Tinkl\Request\CreateInvoice;
use IlCleme\Tinkl\Request\StatusInvoice;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Generic;
use Payum\Core\Storage\IdentityInterface;
use Payum\Core\Tests\GenericActionTest;
use \Payum\Core\Reply\HttpRedirect;

class CaptureActionTest extends GenericActionTest
{
    protected $requestClass = Capture::class;

    protected $actionClass = CaptureAction::class;

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array(new $this->requestClass('foo')),
            array(new $this->requestClass(new \stdClass())),
            array($this->getMockForAbstractClass(Generic::class, array(array()))),
            array($this->getMockForAbstractClass(IdentityInterface::class, array(array()))),
        );
    }
    /**
     * @test
     */
    public function testCaptureNewRequestOnModelWithEmptyStatusThrowHttpRedirect()
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
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CreateInvoice::class))
            ->will($this->returnCallback(function (CreateInvoice $request) {
                $model = $request->getModel();
                $model['status'] = 'statusVal';
                $model['url'] = 'aUrl';
                $request->setModel($model);
            }))
        ;

        $request = new Capture($tokenMock);
        $request->setModel([]);
        $action = new CaptureAction();
        $action->setGateway($gatewayMock);
        $action->setGenericTokenFactory($genericTokenFactory);
        try{
            $action->execute($request);
        } catch (HttpRedirect $httpRedirect) {
            $this->assertInstanceOf(HttpRedirect::class, $httpRedirect);
            $this->assertEquals('aUrl', $httpRedirect->getUrl());
            $this->assertArrayHasKey('notification_url', $request->getModel());
            $this->assertArrayHasKey('redirect_url', $request->getModel());
            $this->assertArrayHasKey('url', $request->getModel());
            $this->assertArrayHasKey('status', $request->getModel());
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
            }))
        ;

        $request = new Capture(['status' => 'pending']);
        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute($request);
        $this->assertArrayHasKey('status', $request->getModel());
        $this->assertArrayHasKey('invoice', $request->getModel());
        $this->assertEquals('fooData', $request->getModel()['invoice']);

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
}
