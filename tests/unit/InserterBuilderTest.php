<?php

namespace Tests\Unit;

use Mockery;
use org\bovigo\vfs\vfsStream;
use App\Http\Services\ImportCsv\InserterBuilder;

class InserterBuilderTest extends \TestCase
{

    public function testOnBuildShouldTryToPersistEloquentModel()
    {
        try {
            InserterBuilder::createInserterForClass('App\Models\GameType')->buildAndSave();
        } catch(\Exception $ex){
            $this->assertTrue(get_class($ex) == 'Illuminate\Database\QueryException');
        }
    }

    public function testOnBuildShouldCallApplyBeforeSaveHookBeforeSaving()
    {
         $fields = array(
            'name' => array('value' => 'A VALUE', 'type' => 'string', 'tables' => array( 'App\Models\GameType'))
        );
        $someFlag = false;
        $spyFunction = function($a) use(&$someFlag) {
                            $this->assertTrue(is_null($a->id)); // No id so its before save
                            $someFlag = !$someFlag;
                            return $a;
                        };
        
        $someInstance = InserterBuilder::createInserterForClass('App\Models\GameType')
                        ->usingHashTable($fields)
                        ->applyingBeforeSaveHook($spyFunction)
                        ->build();
                        
        $this->assertTrue($someFlag); // variable was modified so function was called
    }

    public function testOnBuildShouldFavorAttrNameOverKeyIfProvided()
    {
        $nameValue = 'name_value';
        $fields = array(
            'some_non_existing_attribute' => array('attr_name' => 'name', 'value' => $nameValue, 'type' => 'string', 'tables' => array( 'App\Models\GameType'))
        );
        
        $someInstance = InserterBuilder::createInserterForClass('App\Models\GameType')
                        ->usingHashTable($fields)
                        ->build();
                        
        $this->assertTrue($someInstance->name == $nameValue); 
    }

    public function testShouldThrowExceptionIsRequiredFieldNotPresent()
    {
        $nameValue = '';
        $fields = array(
            'some_non_existing_attribute' => array('attr_name' => 'name', 'value' => $nameValue, 'type' => 'string', 'tables' => array( 'App\Models\GameType'))
        );
        $this->setExpectedException('\Exception');
        
        $someInstance = InserterBuilder::createInserterForClass('App\Models\GameType')
                        ->usingHashTable($fields)
                        ->build();
                        
       
    }

    public function testOnBuildShouldFailIfDateFieldAintParsable() 
    {
        $nameValue = 'lalalalala';
        $fields = array(
            'some_non_existing_attribute' => array('attr_name' => 'name', 'value' => $nameValue, 'type' => 'date', 'tables' => array( 'App\Models\GameType'))
        );
        $this->setExpectedException('\Exception');
        
        $someInstance = InserterBuilder::createInserterForClass('App\Models\GameType')
                        ->usingHashTable($fields)
                        ->build();

    }

    public function testOnBuildShouldParseBooleanAsTrueIfValueIsTRUE() 
    {
        $fields = array(
            'some_non_existing_attribute' => array('attr_name' => 'name', 'value' => 'TRUE', 'type' => 'boolean', 'tables' => array( 'App\Models\GameType'))
        );
       
        
        $someInstance = InserterBuilder::createInserterForClass('App\Models\GameType')
                        ->usingHashTable($fields)
                        ->build();
        
        $this->assertTrue($someInstance->name);
    }

    public function testOnBuildShouldParseBooleanAsFalseIfValueIsNotTRUEnor1() 
    {
        $fields = array(
            'some_non_existing_attribute' => array('attr_name' => 'name', 'value' => 'false', 'type' => 'bool', 'tables' => array( 'App\Models\GameType'))
        );
        
        $someInstance = InserterBuilder::createInserterForClass('App\Models\GameType')
                        ->usingHashTable($fields)
                        ->build();
        
        $this->assertFalse($someInstance->name);
    }
    public function testOnBuildShouldParseBooleanAsTrueIfValueIs1() 
    {
        $fields = array(
            'some_non_existing_attribute' => array('attr_name' => 'name', 'value' => '1', 'type' => 'bool', 'tables' => array( 'App\Models\GameType'))
        );
        
        $someInstance = InserterBuilder::createInserterForClass('App\Models\GameType')
                        ->usingHashTable($fields)
                        ->build();
        
        $this->assertTrue($someInstance->name);
    }

    

}
?>