<?php
namespace XcooBee\Exceptions;

use Exception;

/**
 * XcoobeeException
 */
class XcoobeeException extends Exception
{
    //Redefine the exception so message isn't optional;
    public function __construct($message, $code=0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString(){
        return __CLASS__.": [{$this->code}]: {$this->message}\n";
    }

    public function customFunction(){
        echo "A custom function for this type of exception\n";
    }
}
