<?php

namespace MRussell\Http\Request;

class File extends AbstractRequest
{
    /**
     * @inheritdoc
     */
    protected static $_DEFAULT_HEADERS = array();

    /**
     * @inheritdoc
     */
    protected static $_DEFAULT_HTTP_METHOD = self::HTTP_GET;

    /**
     * @inheritdoc
     */
    protected static $_VALID_HTTP_METHODS = array(
        self::HTTP_GET,
        self::HTTP_POST,
        self::HTTP_PUT
    );

    /**
     * Configure Headers for File Requests based on HTTP Method
     * @inheritdoc
     */
    protected function configureHTTPMethod($method) {
        switch($method){
            case self::HTTP_GET:
                $this->removeHeader('Content-Type');
                break;
            case self::HTTP_POST:
            case self::HTTP_PUT:
                $this->addHeader("Content-Type", "multipart/form-data");
        }
        return parent::configureHTTPMethod($method);
    }

}
