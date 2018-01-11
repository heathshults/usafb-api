<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Exception for expired JWT tokens
 */
class ExpiredTokenException extends UnauthorizedHttpException
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
        return "ExpiredTokenException: [{$this->code}]: {$this->message}\n";
    }
}
