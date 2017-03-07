<?php

namespace MRussell\Http\Tests\Stubs;


use MRussell\Http\Response\AbstractResponse;

class ResponseTestStub extends AbstractResponse
{
    protected static $_CURL_EXTRA_INFO = array(
        CURLINFO_CERTINFO,
        CURLINFO_SSL_ENGINES
    );
}