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
        $this->expectException(\PDOException::class);

        $entity = new Source;
        $entity->name = 'Source Name Test' ;
        $entity->api_key = 'ThisIsMyTestApiKey';

        $entity->save();

        $entity = new Source;
        $entity->name = 'Source Name Test 2' ;
        $entity->api_key = 'ThisIsMyTestApiKey';

        $entity->save();
    }

    /**
    * Should test that a source name is unique
    */
    public function testShouldFailIfNameIsDuplicated() {
        $this->expectException(\PDOException::class);

        $entity = new Source;
        $entity->name = 'Source Name Test' ;
        $entity->api_key = 'ThisIsMyTestApiKey';

        $entity->save();

        $entity = new Source;
        $entity->name = 'Source Name Test' ;
        $entity->api_key = 'ThisIsMyTestApiKey2';

        $entity->save();
    }
}