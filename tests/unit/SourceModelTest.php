<?php

namespace Tests\Unit;

use Mockery;
use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Models\Source;

class SourceModelTest extends \TestCase {
    use DatabaseMigrations;
  
    /**
    * Should test that a source is created successfuly
    */
    public function testShouldCreateASourceSuccessfully() {
        $entity = new Source;
        $entity->name = 'Source Name Test' ;
        $entity->api_key = 'ThisIsMyTestApiKey';

        $entity->save();
        $this->assertTrue(!is_null($entity->id));
    }

    /**
    * Should test that a source api_key is unique
    */
    public function testShouldFailIfApiKeyIsDuplicated() {
        $entity = new Source;
        $entity->name = 'Source Name Test' ;
        $entity->api_key = 'ThisIsMyTestApiKey';

        $entity->save();

        $entity = new Source;
        $entity->name = 'Source Name Test 2' ;
        $entity->api_key = 'ThisIsMyTestApiKey';

        try  {
            $entity->save();
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23505');
        }
    }

    /**
    * Should test that a source name is unique
    */
    public function testShouldFailIfNameIsDuplicated() {
        $entity = new Source;
        $entity->name = 'Source Name Test' ;
        $entity->api_key = 'ThisIsMyTestApiKey';

        $entity->save();

        $entity = new Source;
        $entity->name = 'Source Name Test' ;
        $entity->api_key = 'ThisIsMyTestApiKey2';

        try  {
            $entity->save();
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23505');
        }
    }
}