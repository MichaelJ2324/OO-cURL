<?php

namespace MRussell\Http\Response;

use MRussell\Http\Request\Curl;
use MRussell\Http\Request\RequestInterface;

abstract class AbstractResponse implements ResponseInterface
{
    /**
     * Extra Info to retrieve via curl_getinfo
     * @var array
     */
    protected static $_CURL_EXTRA_INFO = array();

    /**
     * The Curl Request Resource that was used when curl_exec was called
     * @var RequestInterface
     */
    protected $Request;

    /**
     * Extracted headers from cURL Response
     * @var string
     */
    protected $headers;

    /**
     * Extracted body from cURL Response
     * @var mixed
     */
    protected $body;

    /**
     * The HTTP Status Code of Request
     * @var string
     */
    protected $status;

    /**
     * The cURL Resource information returned via curl_getinfo
     * @var array
     */
    protected $info;

    public function __construct(RequestInterface $Request)
    {
        $this->Request = $Request;
        $this->extract();
    }

    public function extract()
    {
        $error = $this->Request->getError();
        if ($this->Request->getStatus() == Curl::STATUS_SENT && empty($error)){
            $this->extractInfo($this->Request->getCurlResource());
            $this->extractResponse($this->Request->getResponse());
            $this->Request->close();
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Extract the information from the Curl Request via curl_getinfo
     * Setup the Status property to be equal to the http_code
     * @param $curlResource - cURL Resource
     */
    protected function extractInfo($curlResource)
    {
        $this->info = curl_getinfo($curlResource);
        foreach(static::$_CURL_EXTRA_INFO as $option){
            $this->info[$option] = curl_getinfo($curlResource,$option);
        }
        $this->status = $this->info['http_code'];
    }

    /**
     * Seperate the Headers and Body from the CurlResponse, and set the object properties
     * @param string $curlResponse
     */
    protected function extractResponse($curlResponse)
    {
        $this->headers = substr($curlResponse, 0, $this->info['header_size']);
        $this->body = substr($curlResponse, $this->info['header_size']);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @inheritdoc
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @inheritdoc
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @inheritdoc
     */
    public function getInfo()
    {
        return $this->info;
    }
}
