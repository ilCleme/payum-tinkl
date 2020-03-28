<?php

namespace IlCleme\Tinkl\Tests\Request;

use IlCleme\Tinkl\Request\StatusInvoice;
use PHPUnit\Framework\TestCase;

class StatusInvoiceTest extends TestCase
{
    public static function provideDifferentPhpTypes()
    {
        return [
            'object' => [new \stdClass()],
            'int' => [5],
            'float' => [5.5],
            'string' => ['foo'],
            'boolean' => [false],
            'resource' => [tmpfile()],
        ];
    }

    /**
     * @test
     */
    public function shouldImplementModelAwareInterface()
    {
        $rc = new \ReflectionClass(StatusInvoice::class);

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\ModelAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementModelAggregateInterface()
    {
        $rc = new \ReflectionClass(StatusInvoice::class);

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\ModelAggregateInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementTokenAggregateInterface()
    {
        $rc = new \ReflectionClass(StatusInvoice::class);

        $this->assertTrue($rc->implementsInterface('Payum\Core\Security\TokenAggregateInterface'));
    }

    /**
     * @test
     *
     * @dataProvider provideDifferentPhpTypes
     */
    public function shouldAllowSetModelAndGetIt($phpType)
    {
        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(StatusInvoice::class, [123321]);

        $request->setModel($phpType);

        $this->assertEquals($phpType, $request->getModel());
    }

    /**
     * @test
     *
     * @dataProvider provideDifferentPhpTypes
     */
    public function shouldAllowGetModelSetInConstructor($phpType)
    {
        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(StatusInvoice::class, [$phpType]);

        $this->assertEquals($phpType, $request->getModel());
    }

    /**
     * @test
     */
    public function shouldAllowGetTokenSetInConstructor()
    {
        $tokenMock = $this->createMock('Payum\Core\Security\TokenInterface');

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(StatusInvoice::class, [$tokenMock]);

        $this->assertSame($tokenMock, $request->getModel());
        $this->assertSame($tokenMock, $request->getToken());
    }

    /**
     * @test
     */
    public function shouldConvertArrayToArrayObjectInConstructor()
    {
        $model = ['foo' => 'bar'];

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(StatusInvoice::class, [$model]);

        $this->assertInstanceOf('ArrayObject', $request->getModel());
        $this->assertEquals($model, (array) $request->getModel());
    }

    /**
     * @test
     */
    public function shouldConvertArrayToArrayObjectSetWithSetter()
    {
        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(StatusInvoice::class, [123321]);

        $model = ['foo' => 'bar'];

        $request->setModel($model);

        $this->assertInstanceOf('ArrayObject', $request->getModel());
        $this->assertEquals($model, (array) $request->getModel());
    }

    /**
     * @test
     */
    public function shouldNotSetTokenAsFirstModelOnConstruct()
    {
        /** @var Generic $request */
        $token = $this->createMock('Payum\Core\Security\TokenInterface');

        $request = $this->getMockForAbstractClass(StatusInvoice::class, [$token]);

        $this->assertNull($request->getFirstModel());
    }

    /**
     * @test
     */
    public function shouldNotSetIdentityAsFirstModelOnConstruct()
    {
        /** @var Generic $request */
        $identity = $this->createMock('Payum\Core\Storage\IdentityInterface', [], [], '', false);

        $request = $this->getMockForAbstractClass(StatusInvoice::class, [$identity]);

        $this->assertNull($request->getFirstModel());
    }

    /**
     * @test
     */
    public function shouldSetAnyObjectAsFirstModelOnConstruct()
    {
        $model = new \stdClass();

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(StatusInvoice::class, [$model]);

        $this->assertSame($model, $request->getFirstModel());
    }

    /**
     * @test
     */
    public function shouldNotSetTokenAsFirstModelOnSetModel()
    {
        $token = $this->createMock('Payum\Core\Security\TokenInterface');

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(StatusInvoice::class, [null]);
        $request->setModel($token);

        $this->assertNull($request->getFirstModel());
    }

    /**
     * @test
     */
    public function shouldNotSetIdentityAsFirstModelOnSetModel()
    {
        $identity = $this->createMock('Payum\Core\Storage\IdentityInterface', [], [], '', false);

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(StatusInvoice::class, [null]);
        $request->setModel($identity);

        $this->assertNull($request->getFirstModel());
    }

    /**
     * @test
     */
    public function shouldSetAnyObjectAsFirstModelOnSetModel()
    {
        $model = new \stdClass();

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(StatusInvoice::class, [null]);
        $request->setModel($model);

        $this->assertSame($model, $request->getFirstModel());
    }

    /**
     * @test
     */
    public function shouldNotChangeFirstModelOnSecondSetModelCall()
    {
        $firstModel = new \stdClass();
        $secondModel = new \stdClass();

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(StatusInvoice::class, [$firstModel]);
        $request->setModel($secondModel);

        $this->assertSame($firstModel, $request->getFirstModel());
        $this->assertSame($secondModel, $request->getModel());
    }
}
