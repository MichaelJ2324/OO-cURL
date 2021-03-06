<?php

namespace MRussell\Http\Request;

interface RequestInterface
{
    /**
     * Set the HTTP Method the Request object will use
     * @param string $type
     * @return self
     */
    public function setMethod($type);

    /**
     * Get the HTTP Method of the Request object
     * @return string
     */
    public function getMethod();

    /**
     * Set the Body to Request
     * @param mixed
     * @return self
     */
    public function setBody($array);

    /**
     * Get the Body on the request
     * @return mixed
     */
    public function getBody();

    /**
     * Add a File to the Request for Upload
     * @param $bodyKey
     * @param $fullFilePath
     * @return self
     */
    public function addFile($bodyKey,$fullFilePath);

    /**
     * Add a Header to the Request Headers property
     * @param string - Header Name
     * @param string - Header Value
     * @return self
     */
    public function addHeader($name, $value);

    /**
     * Append multiple headers to the Request at once
     * @param array $headers
     * @return mixed
     */
    public function addHeaders(array $headers);

    /**
     * Sets the Headers property on the Request object
     * @param array $headers
     * @return self
     */
    public function setHeaders(array $headers);

    /**
     * Remove a Header from the Request Object
     * @param $name
     * @return self
     */
    public function removeHeader($name);

    /**
     * Get the Headers configured on the Request Object
     * @return array - Headers Property
     */
    public function getHeaders();

    /**
     * Add a cURL Option to the Request
     * @param $option
     * @param $value
     * @return self
     */
    public function addOption($option,$value);

    /**
     * Append multiple cURL Options to the Request
     * @param array $options
     * @return self
     */
    public function addOptions(array $options);

    /**
     * Set the cURL Options on the Request
     * @param array $options
     * @return self
     */
    public function setOptions(array $options);

    /**
     * Get the list of Options set on the Curl Resource
     * @return array
     */
    public function getOptions();

    /**
     * Remove a cURL Option from the Request
     * @param $name string
     * @return self
     */
    public function removeOption($name);

    /**
     * Set the URL on the Request Object
     * @param string $url
     * @return self
     */
    public function setURL($url);

    /**
     * Get the URL configured on the Request Object
     * @return string
     */
    public function getURL();

    /**
     * Execute the Curl Request. Before sending, Headers are added to the Curl Object
     * @return self
     */
    public function send();

    /**
     * Get the cURL Resource
     * @return Curl Resource
     */
    public function getCurlResource();

    /**
     * Get the cURL Response Object generated by the Curl Request
     * @return Curl Response Resource
     */
    public function getResponse();

    /**
     * Close the cURL Resource
     * @return self
     */
    public function close();

    /**
     * Close and Restart the cURL Resource
     * @return self
     */
    public function reset();

    /**
     * Get the Status of the Request Object
     * @return integer
     */
    public function getStatus();

    /**
     * Retrieve error that might have occurred on Request
     * @return mixed
     */
    public function getError();
}
