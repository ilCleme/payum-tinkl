<?php
namespace IlCleme\Tinkl\Tests\Action\Api;

use PHPUnit\Framework\TestCase;

class BaseApiAwareActionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('\IlCleme\Tinkl\Action\Api\BaseApiAwareAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('\IlCleme\Tinkl\Action\Api\BaseApiAwareAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass('\IlCleme\Tinkl\Action\Api\BaseApiAwareAction');

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function shouldAllowSetApi()
    {
        $expectedApi = $this->createApiMock();

        $action = $this->getMockForAbstractClass('\IlCleme\Tinkl\Action\Api\BaseApiAwareAction');

        $action->setApi($expectedApi);

        $this->assertAttributeSame($expectedApi, 'api', $action);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwIfUnsupportedApiGiven()
    {
        $action = $this->getMockForAbstractClass('\IlCleme\Tinkl\Action\Api\BaseApiAwareAction');

        $action->setApi(new \stdClass());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('IlCleme\Tinkl\Api', array(), array(), '', false);
    }
}
