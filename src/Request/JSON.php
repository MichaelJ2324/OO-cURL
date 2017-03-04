<?php

namespace MRussell\CURL\Request;

class JSON extends Standard
{
    /**
     * @inheritdoc
     */
    protected static $_DEFAULT_HEADERS = array(
        "Content-Type: application/json"
    );

    /**
     * @inheritdoc
     */
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
     * @inheritdoc
     */
    protected static $_DEFAULT_HTTP_METHOD = 'GET';

    /**
     * @inheritdoc
     */
    protected function configureBody($body) {
        if ($this->method !== self::HTTP_GET){
            $body = json_encode($body);
        }
        return parent::configureBody($body);
    }
}