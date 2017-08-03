<?php
namespace Tests\Unit;

use App\Helpers\FunctionalHelper;

class FunctionalHelperTest extends \TestCase
{
   
    /**
    * Should convert identity to Clojure
    *
    **/
    public function testShouldConvertTestRequiredMethodToClojure()
    {
        $testValue = 'A cool value';
        $isRequiredClojure = FunctionalHelper::toClojure('App\Helpers\FunctionalHelper', 'identity');
        
        $this->assertTrue(is_callable($isRequiredClojure));
        $response = $isRequiredClojure($testValue);
        
        $this->assertEquals($response, $testValue);
    }

    public function testIdentityFunction()
    {
        $value = 'SomeValue';
        $this->assertEquals($value, FunctionalHelper::identity($value));
    }

    public function testShouldComposeTwoFunctions()
    { 
        $func1 = function($v) {
            return $v * 2;
        };

        $func2 = function($v) {
            return $v/2;
        };

        $testData = 8;

        $result = FunctionalHelper::compose($func1, $func2);
        $this->assertEquals($result($testData), $testData);
    }
    
    public function testShouldReturnAFunctionThatRequiresTheSecondParam()
    {
        $testVar1 = 1;
        $testVar2 = 2;
        $testFunction = function($var1, $var2) {
            return $var1 + $var2;
        };
        $curriedFunction = FunctionalHelper::curry2($testFunction, $testVar1);
        
        $result = $curriedFunction($testVar2);
        $result2 = $testFunction($testVar1, $testVar2);
        $this->assertEquals($result, $result2);
    }
    
}