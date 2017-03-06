<?php

namespace MRussell\Http\Request;

class File extends AbstractRequest
{
    protected static $_DEFAULT_HTTP_METHOD = self::HTTP_POST;

    protected static $_VALID_HTTP_METHODS = array(
        Curl::HTTP_GET,
        Curl::HTTP_POST,
        Curl::HTTP_PUT
    );

    protected function configureHTTPMethod($method) {
        switch($method){
            case 'GET':
                $this->removeHeader('Content-Type');
                break;
            case 'POST':
            case 'PUT':
                $this->addHeader("Content-Type", "multipart/form-data");
                break;
        }
        return parent::configureHTTPMethod($method);
    }

}
