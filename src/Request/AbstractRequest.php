<?php

namespace MRussell\CURL\Request\Abstracts;

use MRussell\CURL\Exception\InvalidHttpMethodException;
use MRussell\CURL\Request\RequestInterface;
use MRussell\CURL\Response\ResponseInterface;

abstract class AbstractRequest implements RequestInterface
{
    const STATUS_INIT = 0;
    const STATUS_CURL_INIT = 1;
    const STATUS_SENT = 2;
    const STATUS_CLOSED = 3;
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';
    const HTTP_PUT = 'PUT';
    const HTTP_DELETE = 'DELETE';
    const HTTP_HEAD = 'HEAD';
    const HTTP_OPTIONS = 'OPTIONS';
    const HTTP_CONNECT = 'CONNECT';

    private static $_STATUS_CODES = array(
        0 => 'Initialized',
        1 => 'Curl Initialized',
        2 => 'Sent',
        3 => 'Closed'
    );

    protected static $_VALID_HTTP_METHODS = array(
        self::HTTP_GET,
        self::HTTP_POST,
        self::HTTP_PUT,
        self::HTTP_DELETE,
        self::HTTP_HEAD,
        self::HTTP_OPTIONS,
        self::HTTP_CONNECT
    );

    /**
     * The HTTP Request Method
     * @var string
     */
    protected static $_DEFAULT_HTTP_METHOD = 'GET';

    protected static $_RESPONSE_CLASS = 'MRussell\\CURL\\Response\\Standard';

    /**
     * Whether or not Curl should Initialize when Request Object Does
     * @var bool
     */
    protected static $_AUTO_INIT = FALSE;

    /**
     * The Default Curl Options
     * @var array
     */
    protected static $_DEFAULT_OPTIONS = array(
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0,
        CURLOPT_HEADER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => 'PHP-REST-Client'
    );

    /**
     * The default HTTP Headers to be added to Curl Request
     * @var array
     */
    protected static $_DEFAULT_HEADERS = array();

    /**
     * List of Headers for Request
     * @var array
     */
    protected $headers = array();

    /**
     * The Request Type
     * @var
     */
    protected $method;

    /**
     * The URL the Request is sent to
     * @var string
     */
    protected $url = '';

    /**
     * The body of the request or payload. JSON Encoded
     * @var string
     */
    protected $body = '';

    /**
     * @var integer
     */
    protected $status = 0;

    /**
     * @var ResponseInterface
     */
    protected $Response;

    /**
     * The raw response from curl_exec
     * @var - Curl Response
     */
    protected $CurlResponse;

    /**
     * The Curl Resource used to actually send data
     * @var - Curl Response
     */
    protected $CurlRequest;

    /**
     * The options configured on the Curl Resource object
     * @var array
     */
    protected $CurlOptions = array();

    public function __construct($url = null,$autoInit = FALSE)
    {
        if (!empty($url)) {
            $this->setURL($url);
        }
        $this->setMethod(static::$_DEFAULT_HTTP_METHOD);
        $this->setHeaders(static::$_DEFAULT_HEADERS);
        $this->setOptions(static::$_DEFAULT_OPTIONS);
        static::$_AUTO_INIT = $autoInit;
        $this->start();
    }

    /**
     * Always make sure to destroy Curl Resource
     */
    public function __destruct()
    {
        if ($this->status !== self::STATUS_CLOSED && $this->status > self::STATUS_INIT) {
            curl_close($this->CurlRequest);
        }
    }

    /**
     * @inheritdoc
     */
    public function setURL($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     * @inheritdoc
     */
    public function addHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Add multiple headers via an array
     * @param array $headers
     * @return $this
     */
    public function addHeaders(array $headers)
    {

        foreach ($headers as $key => $value) {
            $header = NULL;
            //Handle Array of String based Headers
            if (is_numeric($key) && strpos($value,":") !== FALSE) {
                $arr = explode(":",$value,2);
                if (count($arr)==2){
                    list($header,$value) = $arr;
                }
            } else {
                $header = $key;
            }
            if (!empty($header)){
                $this->addHeader($key, $value);
            }
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeHeader($name) {
        if (!isset($this->headers[$name])){
            return FALSE;
        }
        unset($this->headers[$name]);
        return TRUE;
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
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addFile($bodyKey,$fullFilePath){
        if (file_exists($fullFilePath) && is_readable($fullFilePath)){
            if (version_compare(PHP_VERSION, '5.5.0') >= 0){
                $File = new \CURLFile($fullFilePath);
            } else {
                $File = '@'.$fullFilePath;
            }
            $this->body[$bodyKey] = $File;
        }
        return $this;
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
    public function getCurlResource()
    {
        return $this->CurlRequest;
    }

    /**
     * @inheritdoc
     */
    public function addOption($option, $value)
    {
        $this->CurlOptions[$option] = $value;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setOptions(array $options){
        $this->CurlOptions = $options;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeOption($name) {
        if (isset($this->CurlOptions[$name])){
            unset($this->CurlOptions[$name]);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOptions()
    {
        return $this->CurlOptions;
    }

    /**
     * @inheritdoc
     */
    public function setMethod($method)
    {
        $method = strtoupper($method);
        if (!in_array($method,static::$_VALID_HTTP_METHODS)){
            throw new InvalidHttpMethodException($method);
        }
        $this->method = $method;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @inheritdoc
     */
    public function send()
    {
        $this->configureCurl();
        $this->Response = curl_exec($this->CurlRequest);
        $this->status = self::STATUS_SENT;
        return $this;
    }

    public function getResponse(){
        return $this->Response;
    }

    /**
     * Actually initialize the cURL Resource and configure All options
     */
    private function configureCurl(){
        $this->initCurl();
        $this->configureHTTPMethod($this->method);
        $this->configureUrl($this->url);
        $this->configureHeaders($this->headers);
        $this->configureBody($this->body);
    }

    /**
     * Configure the URL on the cURL Resource using curl_setopt
     * @param $url
     * @return boolean
     */
    protected function configureUrl($url){
        return curl_setopt($this->CurlRequest,CURLOPT_URL, $url);
    }

    /**
     * Configure the Options on the cURL Resource using curl_setopt_array
     * @param $options array
     * @return boolean
     */
    protected function configureOptions(array $options){
        return curl_setopt_array($this->CurlRequest,$options);
    }

    /**
     * Configure the Body on the cURL Resource
     * @param $body
     * @return bool
     */
    protected function configureBody($body){
        switch ($this->method) {
            case 'GET':
                if (is_array($body) || is_object($body)){
                    $queryParams = http_build_query($body);
                    if (strpos($this->url, "?") === false) {
                        $queryParams = "?".$queryParams;
                    } else {
                        $queryParams = "&".$queryParams;
                    }
                } else {
                    $queryParams = $body;
                }
                return $this->configureUrl($this->url.$queryParams);
            default:
                return curl_setopt($this->CurlRequest,CURLOPT_POSTFIELDS, $body);
        }
    }

    /**
     * Configure the Headers on the cURL Resource
     * @param array $headers
     * @return boolean
     */
    protected function configureHeaders(array $headers){
        return curl_setopt($this->CurlRequest,CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * Configure the Curl Options based on Request Type
     * @param $method
     * @return bool
     */
    protected function configureHTTPMethod($method)
    {
        switch ($method) {
            case 'GET':
                return curl_setopt($this->CurlRequest,CURLOPT_HTTPGET, true);
            case 'POST':
                return curl_setopt($this->CurlRequest,CURLOPT_POST, true);
            case 'PUT':
                return curl_setopt($this->CurlRequest,CURLOPT_PUT,true);
            default:
                return curl_setopt($this->CurlRequest,CURLOPT_CUSTOMREQUEST, $this->method);
        }
    }

    /**
     * @inheritdoc
     */
    public function reset()
    {
        if ($this->status > self::STATUS_CURL_INIT && gettype($this->CurlRequest) == 'resource') {
            $this->close();
        }
        $this->start();
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function start()
    {
        if (static::$_AUTO_INIT){
            $this->initCurl();
        }
        $this->configureResponse();
        $this->status = self::STATUS_INIT;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        $this->closeCurl();
        $this->status = self::STATUS_CLOSED;
        return $this;
    }

    /**
     * @return $this
     */
    private function initCurl(){
        if ($this->status < self::STATUS_CURL_INIT){
            $this->CurlRequest = curl_init();
            $this->status = self::STATUS_CURL_INIT;
        }
        return $this;
    }

    /**
     * @return $this
     */
    private function closeCurl(){
        if ($this->status > self::STATUS_CURL_INIT) {
            curl_close($this->CurlRequest);
            $this->CurlRequest = NULL;
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Return the Human readable representation of the Request status
     */
    public function getStatusString()
    {
        return static::$_STATUS_CODES[$this->status];
    }
}
