<?php

namespace MRussell\Http\Exception;

class RequestException extends \Exception
{
    protected $message = 'Unknown Exception occurred in Request Object [%s]';

    public function __construct($Class) {
        parent::__construct(sprintf($this->message,$Class));
    }
}