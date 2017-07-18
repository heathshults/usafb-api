<?php

namespace Tests\Unit;

use Mockery;
use org\bovigo\vfs\vfsStream;
use App\Http\Services\ImportCsv\InserterBuilder;

class InserterBuilderTest extends \TestCase {

    public function testOnBuildShouldTryToPersistEloquentModel() {
        try {
            InserterBuilder::createInserterForClass('App\Models\GameType')->buildAndSave();
        } catch(\Exception $ex){
            $this->assertTrue(get_class($ex) == 'Illuminate\Database\QueryException');
        }
    }

    public function testOnBuildShouldInsertAndReturnModelToDbWhenFieldsProvided() {
        $fields = array(
            'name' => array('value' => 'A VALUE', 'type' => 'string', 'tables' => array( 'App\Models\GameType'))
        );
        
        $someInstance = InserterBuilder::createInserterForClass('App\Models\GameType')
                        ->usingHashTable($fields)
                        ->buildAndSave();
                        
        $this->assertFalse(is_null($someInstance->id));
    }

    public function testOnBuildShouldCallApplyBeforeSaveHookBeforeSaving(){
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
                        ->buildAndSave();
                        
        $this->assertTrue($someFlag); // variable was modified so function was called
    }

    public function testOnBuildShouldFavorAttrNameOverKeyIfProvided() {
        $nameValue = 'name_value';
        $fields = array(
            'some_non_existing_attribute' => array('attr_name' => 'name', 'value' => $nameValue, 'type' => 'string', 'tables' => array( 'App\Models\GameType'))
        );
        
        $someInstance = InserterBuilder::createInserterForClass('App\Models\GameType')
                        ->usingHashTable($fields)
                        ->buildAndSave();
                        
        $this->assertTrue($someInstance->name == $nameValue); 
    }

    public function testShouldThrowExceptionIsRequiredFieldNotPresent() {
        $nameValue = '';
        $fields = array(
            'some_non_existing_attribute' => array('attr_name' => 'name', 'value' => $nameValue, 'type' => 'string', 'tables' => array( 'App\Models\GameType'))
        );
        $this->setExpectedException('\Exception');
        
        $someInstance = InserterBuilder::createInserterForClass('App\Models\GameType')
                        ->usingHashTable($fields)
                        ->buildAndSave();
                        
       
    }

    public function testOnBuildShouldPartitionByConditionAndReturnABuilderGenerator() {
        $nameValue = 'name_value';
        $fields = array(
            'some_1' => array('attr_name' => 'name', 'value' => $nameValue, 'type' => 'string', 'tables' => array( 'App\Models\GameType')),
            'name_2' => array('attr_name' => 'name', 'value' => 'some_other', 'type' => 'string', 'tables' => array( 'App\Models\GameType'))
        );
        $partitionCondition = function($fieldMetaKey, $fieldMetaValue){
                return strpos($fieldMetaKey,'1') !== false;
            };
        

        $someGenerator = InserterBuilder::createInserterForClass('App\Models\GameType')
                        ->usingHashTable($fields)
                        ->partitionBy($partitionCondition);
        
        foreach ($someGenerator as $value) {
            $instance = $value->buildAndSave();
           $this->assertFalse(is_null($instance->id));
        }
    }

    

}
?>