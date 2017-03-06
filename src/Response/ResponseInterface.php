<?php

namespace MRussell\Http\Response;

interface ResponseInterface
{
    /**
     * Extract the Response information from the Request Object
     * @return mixed
     */
    public function extract();

    /**
     * Get the Response HTTP Status Code
     * @return string
     */
    public function getStatus();

    /**
     * Get the Response Body
     * @return string
     */
    public function getBody();

    /**
     * Get the Response Headers
     * @return string
     */
    public function getHeaders();

    /**
     * Get the Information about the Curl Request
     * @return array
     */
    public function getInfo();

}
