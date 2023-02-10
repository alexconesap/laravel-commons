<?php

namespace Alexconesap\Commons\Exceptions;

class InvalidObjectAttributesException extends BaseException
{

    /**
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}