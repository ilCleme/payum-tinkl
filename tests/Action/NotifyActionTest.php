<?php

namespace IlCleme\Tinkl\Testssss\Action;

use IlCleme\Tinkl\Action\NotifyAction;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Payum\Core\Tests\GenericActionTest;

class NotifyActionTest extends GenericActionTest
{
    protected $requestClass = Notify::class;

    protected $actionClass = NotifyAction::class;

    /**
     * @test
     */
    public function shouldCallGetHttpRequestDuringNotify()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class));

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);
        $action->execute(new Notify([]));
    }
}
