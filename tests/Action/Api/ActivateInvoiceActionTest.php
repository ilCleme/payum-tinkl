<?php

namespace IlCleme\Tinkl\Tests\Action\Api;

use IlCleme\Tinkl\Action\Api\ActivateInvoiceAction;
use IlCleme\Tinkl\Request\ActivateInvoice;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use PHPUnit\Framework\TestCase;

class ActivateInvoiceActionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(ActivateInvoiceAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(ActivateInvoiceAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldSupportActivateInvoiceRequestWithArrayAccessAsModel()
    {
        $action = new ActivateInvoiceAction();

        $this->assertTrue($action->supports(new ActivateInvoice($this->createMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotAuthorizeTokenRequest()
    {
        $action = new ActivateInvoiceAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldCallApiActivateInvoice()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('activateInvoice');

        $action = new ActivateInvoiceAction();
        $action->setApi($apiMock);

        $request = new ActivateInvoice(new \ArrayObject());

        $action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('IlCleme\Tinkl\Api');
    }
}
