<?php

namespace Tests\Unit;

use Mockery;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\Unit\Traits\CreateRelationshipEntities;
use App\Models\Registrant;
use App\Models\Source;
use App\Models\Registration;

class RegistrationModelTest extends \TestCase {
    use DatabaseMigrations;
    use CreateRelationshipEntities;

    /**
    * Should test that a registration is created ok
    */
    public function testShouldGenerateARegistrationSuccessfully() {
        
        $entity = new Registration;
        $entity->source_id = $this->getSource()->id;
        $entity->registrant_id = $this->getRegistrant()->id;
        $entity->type = 'PLAYER' ;
        $entity->league = 'league';
        $entity->org_name = 'Oranization...';
        $entity->org_state = 'NY';
        $entity->season = '2017';
        $entity->external_id = 'myexternalid';
        $entity->right_to_market = true;
        $entity->team_gender = 'Male';
        $entity->team_name = 'A-Team';
        $entity->school_district = 'school district';
        $entity->school_state = 'school sate';
        $entity->first_name = 'Firt name';
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
        $this->assertTrue(!is_null($entity->id));
    }

    /**
    * Should test that a registration creation fail without source
    */
    public function testShouldFailIFARegistrationIsCreatedWithoutSource() {
        $this->expectException(\PDOException::class);
        
        $entity = new Registration;
        $entity->registrant_id = $this->getRegistrant()->id;
        $entity->type = 'PLAYER' ;
        $entity->league = 'league';
        $entity->org_name = 'Oranization...';
        $entity->org_state = 'NY';
        $entity->season = '2017';
        $entity->external_id = 'myexternalid';
        $entity->right_to_market = true;
        $entity->team_gender = 'Male';
        $entity->team_name = 'A-Team';
        $entity->school_district = 'school district';
        $entity->school_state = 'school sate';
        $entity->first_name = 'Firt name';
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
    }

    /**
    * Should test that a registration creation fail without registrant
    */
    public function testShouldFailIFARegistrationIsCreatedWithoutRegistrant() {
        $this->expectException(\PDOException::class);

        $entity = new Registration;
        $entity->source_id = $this->getSource()->id;
        $entity->type = 'PLAYER' ;
        $entity->league = 'league';
        $entity->org_name = 'Oranization...';
        $entity->org_state = 'NY';
        $entity->season = '2017';
        $entity->external_id = 'myexternalid';
        $entity->right_to_market = true;
        $entity->team_gender = 'Male';
        $entity->team_name = 'A-Team';
        $entity->school_district = 'school district';
        $entity->school_state = 'school sate';
        $entity->first_name = 'Firt name';
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
    }
}