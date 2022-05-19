<?php

use apimatic\jsonmapper\JsonMapperException;
use apimatic\jsonmapper\JsonMapper;

class JsonMapperCommentsDiscardedException extends JsonMapper
{
    /**
     * @throws JsonMapperException
     */

    function __construct($config)
    {
        $this->config = $config;

        parent::__construct();
    }
}
?>