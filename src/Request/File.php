<?php

namespace MRussell\CURL\Request;

class File extends Standard
{
    protected static $_DEFAULT_HTTP_METHOD = 'POST';

    protected static $_VALID_HTTP_METHODS = array(
        Standard::HTTP_GET,
        Standard::HTTP_POST,
        Standard::HTTP_PUT
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
