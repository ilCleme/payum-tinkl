<?php

namespace IlCleme\Tinkl;

use Http\Message\MessageFactory;
use IlCleme\Tinkl\Exception\TinklException;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\HttpClientInterface;

class Api
{
    const ENDPOINT_PRODUCTION = 'http://api.tinkl.it';
    const ENDPOINT_SANDBOX = 'http://api-staging.tinkl.it';
    const DEFAULT_VERSION = 'v1';

    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     * @param MessageFactory      $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $options = ArrayObject::ensureArrayObject($options);
        $options->defaults($this->options);
        $options->validateNotEmpty([
            'clientId',
            'token',
        ]);

        if (false == is_bool($options['sandbox'])) {
            throw new InvalidArgumentException('The boolean sandbox option must be set.');
        }

        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    public function createInvoice(array $parameter)
    {
        return $this->doRequest('POST', $this->getEndpoint('invoices'), $parameter);
    }

    public function activateInvoice(array $parameter)
    {
        $activationUrl = array_key_exists('activation_url', $parameter) ? $parameter['activation_url'] : null;
        if (! $activationUrl) {
            $guidInvoice = array_key_exists('guid', $parameter) ? $parameter['guid'] : null;
            $activationUrl = $this->getEndpoint('invoices/'.$guidInvoice).'/activate';
        }

        return $this->doRequest('PATCH', $activationUrl, $parameter);
    }

    public function getStatusInvoice(array $parameter)
    {
        $guidInvoice = array_key_exists('guid', $parameter) ? $parameter['guid'] : null;

        return $this->doRequest('GET', $this->getEndpoint('invoices/'.$guidInvoice), $parameter);
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array $fields
     *
     * @return array
     */
    protected function doRequest($method, $endpoint, array $fields)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-CLIENT-ID' => $this->options['clientId'],
            'X-AUTH-TOKEN' => $this->options['token'],
        ];

        $request = $this->messageFactory->createRequest($method, $endpoint, $headers, json_encode($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw TinklException::factory($request, $response);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param $segments
     * @return string
     */
    public function getEndpoint($segments = '')
    {
        return implode(DIRECTORY_SEPARATOR, [$this->getApiEndpoint(), $this->getApiVersion(), $segments]);
    }

    /**
     * @return array
     */
    protected function getCredential()
    {
        return [
            'X-AUTH-TOKEN' => $this->options['token'],
            'X-CLIENT-ID' => $this->options['client_id'],
        ];
    }

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        return $this->options['sandbox'] ? static::ENDPOINT_SANDBOX : static::ENDPOINT_PRODUCTION;
    }

    /**
     * @return string
     */
    protected function getApiVersion()
    {
        return $this->options['version'] ?: self::DEFAULT_VERSION;
    }
}
