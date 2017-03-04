<?php

namespace Mrussell\CURL\Exception;

class InvalidHttpMethodException extends RequestException
{
    protected $message = 'Invalid HTTP Method: [%s]';
}