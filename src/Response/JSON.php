<?php

namespace MRussell\Http\Response;

class JSON extends Standard
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
