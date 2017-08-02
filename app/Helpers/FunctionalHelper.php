<?php
namespace App\Helpers;

/**
* Common Functional programming functions that php dont provide
*/
class FunctionalHelper
{
    /**
    * Will return the same value passed to identity
    * @param mixed $arg a value
    * @return The same value passed in
    */
    public static function identity($arg)
    {
        return $arg;
    }

    /**
    * Will take one or more functions as params
    * Every function passed to be composed, must only take one argument
    * @param mixed $args
    * @return a function composed by the functions as param
    */
    public static function compose()
    {
        $args = func_get_args();
        return array_reduce($args, function ($acumFunc, $currentFunc) {
                return function ($arg) use ($acumFunc, $currentFunc) {
                    return $currentFunc($acumFunc($arg));
                };
        }, self::toClojure('App\Helpers\FunctionalHelper', 'identity'));
    }
    /**
    * Will return a function that takes a second argument
    * number 2 on the function name is because this curry version supports 2 and only 2 arguments
    * @param function $func function to curry
    * @param mixed $arg1 First argument of the given function
    * @return a the function received in the first argument expecting the second param
    */
    public static function curry2($func, $arg1)
    {
        return function ($arg2) use ($func, $arg1) {
            return $func($arg1, $arg2);
        };
    }

    /**
    * Will return an anonimous function
    * @param String $className full namespace and class name for static function
    * @param String $methodName method of function to convert to clojure
    */
    public static function toClojure($className, $methodName)
    {
        return array($className, $methodName);
    }
}
