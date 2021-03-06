<?php

namespace IlCleme\Tinkl\Tests\Action;

use IlCleme\Tinkl\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;

class StatusActionTest extends GenericActionTest
{
    protected $pendingStatus = ['pending', 'partial', 'deferred'];

    /**
     * @var GetHumanStatus
     */
    protected $requestClass = GetHumanStatus::class;

    /**
     * @var string
     */
    protected $actionClass = StatusAction::class;

    /**
     * @test
     */
    public function testMarkNewRequestWithoutModelState()
    {
        $model = new ArrayObject([]);
        $request = new $this->requestClass($model);
        $request->setModel($model);
        $this->action->execute($request);

        $this->assertEquals($request->getValue(), 'new');
    }

    /**
     * @test
     */
    public function testMarkUnknownRequestWithNotValidStatus()
    {
        $model = new ArrayObject(['status' => 'notvalidstatus']);
        $request = new $this->requestClass($model);
        $request->setModel($model);
        $this->action->execute($request);

        $this->assertEquals($request->getValue(), 'unknown');
    }

    /**
     * @test
     */
    public function testMarkPendingRequestWith()
    {
        $model = new ArrayObject();
        $request = new $this->requestClass($model);

        foreach ($this->pendingStatus as $status) {
            $model->replace(['status' => $status]);
            $request->setModel($model);
            $this->action->execute($request);

            $this->assertEquals($request->getValue(), 'pending');
        }
    }

    /**
     * @test
     */
    public function testMarkFailedRequestWithErrorStatus()
    {
        $model = new ArrayObject(['status' => 'error']);
        $request = new $this->requestClass($model);
        $request->setModel($model);
        $this->action->execute($request);

        $this->assertEquals($request->getValue(), 'failed');
    }

    /**
     * @test
     */
    public function testMarkExpiredRequestWithExpiresStatus()
    {
        $model = new ArrayObject(['status' => 'expired']);
        $request = new $this->requestClass($model);
        $request->setModel($model);
        $this->action->execute($request);

        $this->assertEquals($request->getValue(), 'expired');
    }
}
