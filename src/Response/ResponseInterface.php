<?php

namespace MRussell\Http\Response;

use MRussell\Http\Request\RequestInterface;

interface ResponseInterface
{
    /**
     * Set the Request Object that the Response is extracted from
     * @param RequestInterface $Request
     * @return self
     */
    public function setRequest(RequestInterface $Request);

    /**
     * Get the current configured Request Object
     * @return RequestInterface
     */
    public function getRequest();

    /**
     * Extract the Response information from the Request Object
     * @return bool
     */
    public function extract();

    /**
     * Get the Response HTTP Status Code
     * @return string
     */
    public function getStatus();

    /**
     * Get the Response Body
     * @return mixed
     */
    public function getBody();

    /**
     * Get the Response Headers
     * @return mixed
     */
    public function getHeaders();

    /**
     * Get the Information about the Curl Request
     * @return array
     */
    public function getInfo();

}
