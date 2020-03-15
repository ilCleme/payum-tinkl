<?php
namespace IlCleme\Tinkl\Tests;

use IlCleme\Tinkl\Api;
use PHPUnit\Framework\TestCase;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Payum\Core\HttpClientInterface;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7\Response;

class ApiTest extends TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithOptionsAndHttpClient()
    {
        $client = $this->createHttpClientMock();
        $factory = $this->createHttpMessageFactory();

        $api = new Api(array(
            'clientId' => 'aclientId',
            'token' => 'aToken',
            'version' => 'v1',
            'deferred' => false,
            'sandbox' => true,
        ), $client, $factory);

        $this->assertAttributeSame($client, 'client', $api);
        $this->assertAttributeSame($factory, 'messageFactory', $api);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The clientId, token fields are required.
     */
    public function throwIfRequiredOptionsNotSetInConstructor()
    {
        new Api(array(), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The boolean sandbox option must be set.
     */
    public function throwIfSandboxOptionNotIsBool()
    {
        new Api(array(
            'clientId' => 'aclientId',
            'token' => 'aToken',
            'sandbox' => 'notBoolean',
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    /**
     * @test
     */
    public function shouldUseRealApiEndpointIfSandboxFalse()
    {
        $testCase = $this;

        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) use ($testCase) {
                $testCase->assertEquals('http://api.tinkl.it/v1/invoices', $request->getUri()->__toString());

                return new Response(200, [], $request->getBody());
            }))
        ;

        $api = new Api(array(
            'clientId' => 'aclientId',
            'token' => 'aToken',
            'version' => 'v1',
            'deferred' => false,
            'sandbox' => false,
        ), $clientMock, $this->createHttpMessageFactory());

        $api->createInvoice(array());
    }

    /**
     * @test
     */
    public function shouldUseSandboxApiEndpointIfSandboxTrue()
    {
        $testCase = $this;

        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) use ($testCase) {
                $testCase->assertEquals('http://api-staging.tinkl.it/v1/invoices', $request->getUri()->__toString());

                return new Response(200, [], $request->getBody());
            }))
        ;

        $api = new Api(array(
            'clientId' => 'aclientId',
            'token' => 'aToken',
            'version' => 'v1',
            'deferred' => false,
            'sandbox' => true,
        ), $clientMock, $this->createHttpMessageFactory());

        $api->createInvoice(array());
    }

    /**
     * @test
     */
    public function getEndpointWithRightSegmentOnProductionEnvironment()
    {
        $api = new Api(array(
            'clientId' => 'aclientId',
            'token' => 'aToken',
            'sandbox' => true,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $parameter = 'test/test/';
        $endpoint = $api->getEndpoint($parameter);
        $this->assertIsString($endpoint);
        $this->assertStringStartsWith($api::ENDPOINT_SANDBOX, $endpoint);
        $this->assertStringEndsWith($parameter, $endpoint);
    }

    /**
     * @test
     */
    public function getEndpointWithRightSegmentOnSandboxEnvironment()
    {
        $api = new Api(array(
            'clientId' => 'aclientId',
            'token' => 'aToken',
            'sandbox' => false,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $parameter = 'test/test/';
        $endpoint = $api->getEndpoint($parameter);
        $this->assertIsString($endpoint);
        $this->assertStringStartsWith($api::ENDPOINT_PRODUCTION, $endpoint);
        $this->assertStringEndsWith($parameter, $endpoint);
    }

    /**
     * @test
     */
    public function getEndpointReturnAStringStartsWithProductionEndpoint()
    {
        $api = new Api(array(
            'clientId' => 'aclientId',
            'token' => 'aToken',
            'sandbox' => false,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertIsString($api->getEndpoint());
        $this->assertStringStartsWith($api::ENDPOINT_PRODUCTION, $api->getEndpoint());
    }

    /**
     * @test
     */
    public function getEndpointReturnAStringStartsWithSandboxEndpoint()
    {
        $api = new Api(array(
            'clientId' => 'aclientId',
            'token' => 'aToken',
            'sandbox' => true,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertIsString($api->getEndpoint());
        $this->assertStringStartsWith($api::ENDPOINT_SANDBOX, $api->getEndpoint());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpClientInterface
     */
    protected function createHttpClientMock()
    {
        return $this->createMock('Payum\Core\HttpClientInterface');
    }

    /**
     * @return \Http\Message\MessageFactory
     */
    protected function createHttpMessageFactory()
    {
        return new GuzzleMessageFactory();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpClientInterface
     */
    protected function createSuccessHttpClientStub()
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->any())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) {
                return new Response(200, [], $request->getBody());
            }))
        ;

        return $clientMock;
    }
}
