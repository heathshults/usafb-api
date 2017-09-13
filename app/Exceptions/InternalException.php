<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception for Internal Server errors whose message
 * is going to be shown to end user
 */
class InternalException extends Exception
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
        return "InternalException: [{$this->code}]: {$this->message}\n";
    }
}
