<?php

namespace MRussell\CURL\Response;

use MRussell\CURL\Response\Abstracts\AbstractResponse;

class JSON extends AbstractResponse
{
    /**
     * Get JSON Response
     */
    public function getJson()
    {
        return $this->body;
    }

    /**
     * @inheritdoc
     */
    public function getBody($asArray = true)
    {
        return json_decode($this->body, $asArray);
    }
}
