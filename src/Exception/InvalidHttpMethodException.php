<?php

namespace MRussell\Http\Exception;

class InvalidHttpMethodException extends RequestException
{
    protected $message = 'Invalid HTTP Method: [%s]';
}