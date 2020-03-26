<?php
namespace IlCleme\Tinkl\Testssss\Action\Api;

use IlCleme\Tinkl\Action\Api\StatusInvoiceAction;
use IlCleme\Tinkl\Request\StatusInvoice;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use PHPUnit\Framework\TestCase;

class StatusInvoiceActionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(StatusInvoiceAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(StatusInvoiceAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldSupportCreateInvoiceRequestWithArrayAccessAsModel()
    {
        $action = new StatusInvoiceAction();

        $this->assertTrue($action->supports(new StatusInvoice($this->createMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotAuthorizeTokenRequest()
    {
        $action = new StatusInvoiceAction();

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
            ->method('getStatusInvoice');

        $action = new StatusInvoiceAction();
        $action->setApi($apiMock);

        $request = new StatusInvoice(new \ArrayObject());

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
