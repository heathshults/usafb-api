<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception for ElasticsearchService errors
 */
class ElasticsearchException extends Exception
{
    /**
    * Redefine the exception so message isn't optional
    *
    * @param string $message Custom exception message
    * @param int $code Error status code
    * @param Exception $previous Previous exception if nested exception
    *
    * @return void
    */
    public function __construct($message, $code = 500, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
    * Custom string representation of exception
    *
    * @return string
    */
    public function __toString()
    {
        return "ElasticsearchException: [{$this->code}]: {$this->message}\n";
    }
}
