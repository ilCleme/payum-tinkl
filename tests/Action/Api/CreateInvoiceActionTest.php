<?php

namespace IlCleme\Tinkl\Tests\Action\Api;

use IlCleme\Tinkl\Action\Api\CreateInvoiceAction;
use IlCleme\Tinkl\Request\CreateInvoice;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use PHPUnit\Framework\TestCase;

class CreateInvoiceActionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(CreateInvoiceAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(CreateInvoiceAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldSupportCreateInvoiceRequestWithArrayAccessAsModel()
    {
        $action = new CreateInvoiceAction();

        $this->assertTrue($action->supports(new CreateInvoice($this->createMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotAuthorizeTokenRequest()
    {
        $action = new CreateInvoiceAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldCallApiCreateInvoice()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('createInvoice');

        $action = new CreateInvoiceAction();
        $action->setApi($apiMock);

        $request = new CreateInvoice(new \ArrayObject());

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
