<?php

namespace MRussell\CURL\Response\Abstracts;

use MRussell\CURL\Request\Standard;
use MRussell\CURL\Request\RequestInterface;
use MRussell\CURL\Response\ResponseInterface;

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
     * The last Curl Error that occurred
     * @var string|boolean - False when cURL Error = 0
     */
    protected $error;

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
        if ($this->Request->getStatus() == Standard::STATUS_SENT){
            $this->extractInfo($this->Request->getCurlResource());
            $this->extractResponse($this->Request->getCurlResponse());
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
        if (curl_errno($curlResource)!== CURLE_OK) {
            $this->error = curl_error($curlResource);
        } else {
            $this->error = false;
        }
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
    public function getError()
    {
        return $this->error;
    }

    /**
     * @inheritdoc
     */
    public function getInfo()
    {
        return $this->info;
    }
}
