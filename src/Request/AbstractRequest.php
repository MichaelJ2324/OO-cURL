<?php

namespace MRussell\Http\Request;

use MRussell\Http\Exception\InvalidHttpMethodException;
use MRussell\Http\Request\RequestInterface;

abstract class AbstractRequest implements RequestInterface
{
    const STATUS_INIT = 0;
    const STATUS_CURL_INIT = 1;
    const STATUS_SENT = 2;
    const STATUS_CLOSED = 3;
    const STATUS_ERROR = 10;

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
    protected static $_DEFAULT_HTTP_METHOD = self::HTTP_GET;

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
        CURLOPT_HEADER => TRUE,
        CURLOPT_SSL_VERIFYPEER => FALSE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_FOLLOWLOCATION => TRUE
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
     * The body of the request
     * @var mixed
     */
    protected $body;

    /**
     * The options to be configured on the Request
     * @var array
     */
    protected $options = array();

    /**
     * @var bool
     */
    protected $error = FALSE;

    /**
     * @var integer
     */
    protected $status = 0;

    /**
     * Marks Request as containing a File, so that Request Body is properly set
     * @var bool
     */
    protected $upload = FALSE;

    /**
     * The raw response from curl_exec
     * @var mixed
     */
    protected $CurlResponse = NULL;

    /**
     * The Curl Resource used to actually send data
     * @var resource
     */
    protected $CurlRequest = NULL;

    /**
     * The options configured on the Curl Resource object
     * @var array
     */
    protected $CurlOptions = array();

    /**
     * @var array
     */
    protected $CurlError = array();


    public function __construct($url = null,$httpMethod = NULL)
    {
        $this->init();
        if ($url !== NULL) {
            $this->setURL($url);
        }
        if ($httpMethod !== NULL){
            $this->setMethod($httpMethod);
        }
    }

    /**
     * Always make sure to destroy Curl Resource
     */
    public function __destruct()
    {
        $this->closeCurl();
    }

    /**
     * Get or Set the cURL Auto Init Setting for Request Object
     * @param null $autoInit
     * @return bool
     */
    public static function autoInit($autoInit = NULL){
        if ($autoInit !== NULL){
            static::$_AUTO_INIT = boolval($autoInit);
        }
        return static::$_AUTO_INIT;
    }

    /**
     * Get or Set the Default Options for Request Object
     * @param null $options
     * @return array
     */
    public static function defaultOptions($options = NULL){
        if (is_array($options)){
            static::$_DEFAULT_OPTIONS = $options;
        }
        return static::$_DEFAULT_OPTIONS;
    }

    /**
     * Get or Set the Default Headers for Request Object
     * @param null $headers
     * @return array
     */
    public static function defaultHeaders($headers = NULL){
        if (is_array($headers)){
            static::$_DEFAULT_HEADERS = $headers;
        }
        return static::$_DEFAULT_HEADERS;
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
                    $header = $arr[0];
                    $value = trim($arr[1]);
                }
            } else {
                $header = $key;
            }
            if ($header !== NULL){
                $this->addHeader($header, $value);
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
        if (isset($this->headers[$name])){
            unset($this->headers[$name]);
        }
        return $this;
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
            $this->upload = TRUE;
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
    public function addOption($option, $value)
    {
        $this->options[$option] = $value;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addOptions(array $options)
    {
        foreach($options as $option => $value){
            $this->addOption($option,$value);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setOptions(array $options){
        $this->options = $options;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeOption($name) {
        if (isset($this->options[$name])){
            unset($this->options[$name]);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOptions()
    {
        return $this->options;
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
        if ($this->executeCurl()){
            $this->status = self::STATUS_SENT;
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCurlResource()
    {
        return $this->CurlRequest;
    }

    /**
     * @return mixed
     */
    public function getResponse(){
        return $this->CurlResponse;
    }

    /**
     * @return array
     */
    public function getCurlOptions(){
        return $this->compileOptions()->CurlOptions;
    }

    /**
     * Actually initialize the cURL Resource and configure All options
     */
    private function compileOptions(){
        $this->CurlOptions = array();
        $this->configureHTTPMethod($this->method);
        $this->configureUrl($this->url);
        $this->configureBody($this->body);
        $this->configureHeaders($this->headers);
        $this->configureOptions($this->options);
        return $this;
    }

    /**
     * Configure the Curl Options based for a specific HTTP Method
     * @param $method
     * @return bool
     */
    protected function configureHTTPMethod($method)
    {
        switch ($method) {
            case 'GET':
                return $this->addCurlOption(CURLOPT_HTTPGET, true);
            case 'POST':
                return $this->addCurlOption(CURLOPT_POST, true);
            case 'PUT':
                return $this->addCurlOption(CURLOPT_PUT,true);
            default:
                return $this->addCurlOption(CURLOPT_CUSTOMREQUEST, $method);
        }
    }

    /**
     * Configure the URL by setting the CURLOPT_URL Option
     * @param $url
     * @return boolean
     */
    protected function configureUrl($url){
        return $this->addCurlOption(CURLOPT_URL, $url);
    }

    /**
     * Configure the Headers by setting the CURLOPT_HTTPHEADER Option
     * @param array $headers
     * @return boolean
     */
    protected function configureHeaders(array $headers){
        $configuredHeaders = array();
        foreach($headers as $header => $value){
            $configuredHeaders[] = "$header: $value";
        }
        return $this->addCurlOption(CURLOPT_HTTPHEADER, $configuredHeaders);
    }

    /**
     * Configure the Body to be set on the cURL Resource
     * @param $body
     * @return bool
     */
    protected function configureBody($body){
        switch ($this->method) {
            case self::HTTP_GET:
                if (!empty($body) && !$this->upload){
                    if (is_array($body) || is_object($body)){
                        $queryParams = http_build_query($body);
                    } else {
                        $queryParams = $body;
                    }
                    if (strpos($this->url, "?") === false) {
                        $queryParams = "?".$queryParams;
                    } else {
                        $queryParams = "&".$queryParams;
                    }
                    return $this->configureUrl($this->url.$queryParams);
                }
            default:
                if ($this->upload){
                    $this->addHeader('Content-Type','multipart/form-data');
                }
                return $this->addCurlOption(CURLOPT_POSTFIELDS, $body);
        }
    }

    /**
     * @param $options
     */
    protected function configureOptions($options){
        foreach($options as $option => $value){
            $this->addCurlOption($option,$value);
        }
    }

    /**
     * Configure an option to be set on the cURL Resource
     * @param $option
     * @param $value
     * @return boolean
     */
    protected function addCurlOption($option, $value){
        $this->CurlOptions[$option] = $value;
        return $this;
    }

    /**
     * Initialize the Request Object, setting defaults for certain properties
     * @return bool
     */
    protected function init()
    {
        $this->setMethod(static::$_DEFAULT_HTTP_METHOD);
        $this->setHeaders(static::$_DEFAULT_HEADERS);
        $this->setOptions(static::$_DEFAULT_OPTIONS);
        $this->body = NULL;
        $this->error = NULL;
        $this->status = self::STATUS_INIT;
        if (self::$_AUTO_INIT){
            return $this->initCurl();
        }
        return TRUE;
    }

    /**
     * @inheritdoc
     */
    public function reset()
    {
        if ($this->status > self::STATUS_CURL_INIT) {
            $this->close();
        }
        $this->init();
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
    private function initCurl()
    {
        if ($this->status < self::STATUS_CURL_INIT){
            $this->CurlRequest = curl_init();
            $this->status = self::STATUS_CURL_INIT;
        }
        return ($this->status == self::STATUS_CURL_INIT);
    }

    /**
     * Loop through CurlOptions and use curl_setopt to set options on cURL Resource
     * @return $this
     */
    private function configureCurl()
    {
        foreach($this->getCurlOptions() as $option => $value){
            curl_setopt($this->CurlRequest,$option,$value);
        }
        return TRUE;
    }

    /**
     * Execute Curl Resource and set the Curl Response
     * @return $this
     */
    private function executeCurl()
    {
        if ($this->initCurl()){
            $this->configureCurl();
            $this->CurlResponse = curl_exec($this->CurlRequest);
            $this->checkForError();
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Check cURL Resource for Errors and add them to CurlError property if so
     */
    private function checkForError(){
        $curlErrNo = curl_errno($this->CurlRequest);
        if ($curlErrNo !== CURLE_OK) {
            $this->error = TRUE;
            $this->CurlError = array(
                'error' => $curlErrNo,
                'error_message' => curl_error($this->CurlRequest)
            );
        }
    }

    /**
     * @return $this
     */
    private function closeCurl()
    {
        if (gettype($this->CurlRequest) == 'resource') {
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
        return self::$_STATUS_CODES[$this->status];
    }

    /**
     * Returns if an error occurred or not
     * @return bool
     */
    public function error(){
        return $this->error;
    }

    /**
     * Returns the Error details
     * @return array
     */
    public function getError(){
        return $this->CurlError;
    }
}
