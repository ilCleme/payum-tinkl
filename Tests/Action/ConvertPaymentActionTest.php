<?php
namespace IlCleme\Tinkl\Tests\Action;

use IlCleme\Tinkl\Action\ConvertPaymentAction;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\Token;
use Payum\Core\Tests\GenericActionTest;
use Payum\Core\Request\Convert;

class ConvertPaymentActionTest extends GenericActionTest
{
    /**
     * @var Convert
     */
    protected $requestClass = Convert::class;

    /**
     * @var string
     */
    protected $actionClass = ConvertPaymentAction::class;

    public function provideSupportedRequests()
    {
        return [
            [new $this->requestClass(new Payment(), 'array')],
            [new $this->requestClass($this->createMock(PaymentInterface::class), 'array')],
            [new $this->requestClass(new Payment(), 'array', $this->createMock('Payum\Core\Security\TokenInterface'))],
        ];
    }

    public function provideNotSupportedRequests()
    {
        return [
            ['foo'],
            [['foo']],
            [new \stdClass()],
            [$this->getMockForAbstractClass('Payum\Core\Request\Generic', [[]])],
            [new $this->requestClass(new \stdClass(), 'array')],
            [new $this->requestClass(new Payment(), 'foobar')],
            [new $this->requestClass($this->createMock(PaymentInterface::class), 'foobar')],
        ];
    }

    /**
     * @test
     */
    public function shouldCorrectlyConvertOrderToDetailsAndSetItBack()
    {
        $token = $this->createMock('Payum\Core\Security\TokenInterface');
        $order = new Payment();
        $order->setNumber('theNumber');
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');
        $order->setClientId('theClientId');
        $order->setClientEmail('theClientEmail');

        $action = new ConvertPaymentAction();

        $action->execute($convert = new Convert($order, 'array', $token));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('price', $details);
        $this->assertEquals(123, $details['price']);

        $this->assertArrayHasKey('currency', $details);
        $this->assertEquals('USD', $details['currency']);

        $this->assertArrayHasKey('number', $details);
        $this->assertEquals('theNumber', $details['number']);

        $this->assertArrayHasKey('description', $details);
        $this->assertEquals('the description', $details['description']);

        $this->assertArrayHasKey('client_id', $details);
        $this->assertEquals('theClientId', $details['client_id']);

        $this->assertArrayHasKey('client_email', $details);
        $this->assertEquals('theClientEmail', $details['client_email']);
    }

    /**
     * @test
     */
    public function shouldNotOverwriteAlreadySetExtraDetails()
    {
        $order = new Payment();
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');
        $order->setDetails(array(
            'test' => 'testVal',
        ));

        $action = new ConvertPaymentAction();

        $action->execute($convert = new Convert($order, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('test', $details);
        $this->assertEquals('testVal', $details['test']);
    }
}
