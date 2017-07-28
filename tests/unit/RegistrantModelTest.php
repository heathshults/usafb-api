<?php

namespace Tests\Unit;

use Mockery;
use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Models\Registrant;

class RegistrantModelTest extends \TestCase {
    use DatabaseMigrations;
  
    /**
    * Should test that an ussf_id is generated on create
    */
    public function testShouldGenerateAnUssfIdAfterSaving() {
        $entity = new Registrant;
        $entity->type = 'PLAYER' ;
        $entity->first_name = 'Some name';
        $entity->middle_name = 'Middle name';
        $entity->last_name = 'Last Name';
        $entity->email = 'mail@mail.com';
        $entity->gender = 'Male';
        $entity->city = 'California';
        $entity->zip_code = '234141234123';
        $entity->birth_date = '11/27/1984';
        $entity->phone_number = '1234567890';
        $entity->game_type = 'SOME';
        $entity->level_of_play = 'LEVEL';
        $entity->state = 'CALIFORNIA';
        $entity->address_first_line = 'An Address 1234';
        $entity->county = 'A county';

        $entity->save();
        $this->assertTrue(!is_null($entity->usadfb_id));
    }
}