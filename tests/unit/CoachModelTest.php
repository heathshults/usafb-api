<?php

namespace Tests\Unit;

use Mockery;
use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Models\Coach;
use App\Models\Registrant;

class CoachModelTest extends \TestCase {
    use DatabaseMigrations;
  
    /**
    * Should test that an ussf_id is generated on create
    */
    public function testShouldGenerateAnUsafbIdAfterSaving() {

        $entityRegistrant = new Registrant;
        $entityRegistrant->type = 'COACH' ;
        $entityRegistrant->first_name = 'Some name';
        $entityRegistrant->middle_name = 'Middle name';
        $entityRegistrant->last_name = 'Last Name';
        $entityRegistrant->email = 'mail@mail.com';
        $entityRegistrant->gender = 'Male';
        $entityRegistrant->city = 'California';
        $entityRegistrant->zip_code = '234141234123';
        $entityRegistrant->birth_date = '11/27/1984';
        $entityRegistrant->phone_number = '1234567890';
        $entityRegistrant->game_type = 'SOME';
        $entityRegistrant->level_of_play = 'LEVEL';
        $entityRegistrant->state = 'CALIFORNIA';
        $entityRegistrant->address_first_line = 'An Address 1234';
        $entityRegistrant->county = 'A county';

        $entityCoach = new Coach;
        $entityCoach->certifications = 'Certification 1, certification 2, cert 3...';
        $entityCoach->roles = 'Role 1, role 2, role 3...';
        $entityCoach->years_of_experience = '5';

        $entityRegistrant->save();
        $entityRegistrant->coach()->save($entityCoach);

        $this->assertTrue(!is_null($entityCoach->usadfb_id));
    }

    /**
    * Should test that a coach couldn't be created without it's parent
    */
    public function testShouldFailIfCoachIsCreatedWithoutARegistrant() {
        $entityCoach = new Coach;
        $entityCoach->certifications = 'Certification 1, certification 2, cert 3...';
        $entityCoach->roles = 'Role 1, role 2, role 3...';
        $entityCoach->years_of_experience = '5';

        try  {
            $entityCoach->save();
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a coach can be created if the certification field is null
    */
    public function testShouldNotFailIfTheRequiredFieldCertificationIsNull() {

        $entityRegistrant = new Registrant;
        $entityRegistrant->type = 'COACH' ;
        $entityRegistrant->first_name = 'Some name';
        $entityRegistrant->middle_name = 'Middle name';
        $entityRegistrant->last_name = 'Last Name';
        $entityRegistrant->email = 'mail@mail.com';
        $entityRegistrant->gender = 'Male';
        $entityRegistrant->city = 'California';
        $entityRegistrant->zip_code = '234141234123';
        $entityRegistrant->birth_date = '11/27/1984';
        $entityRegistrant->phone_number = '1234567890';
        $entityRegistrant->game_type = 'SOME';
        $entityRegistrant->level_of_play = 'LEVEL';
        $entityRegistrant->state = 'CALIFORNIA';
        $entityRegistrant->address_first_line = 'An Address 1234';
        $entityRegistrant->county = 'A county';

        $entityCoach = new Coach;
        $entityCoach->roles = 'Role 1, role 2, role 3...';
        $entityCoach->years_of_experience = '5';

        try  {
            $entityRegistrant->save();
            $entityRegistrant->coach()->save($entityCoach);
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a player couldn't be created if the roles field is null
    */
    public function testShouldFailIfTheRequiredFieldRolesIsNull() {

        $entityRegistrant = new Registrant;
        $entityRegistrant->type = 'COACH' ;
        $entityRegistrant->first_name = 'Some name';
        $entityRegistrant->middle_name = 'Middle name';
        $entityRegistrant->last_name = 'Last Name';
        $entityRegistrant->email = 'mail@mail.com';
        $entityRegistrant->gender = 'Male';
        $entityRegistrant->city = 'California';
        $entityRegistrant->zip_code = '234141234123';
        $entityRegistrant->birth_date = '11/27/1984';
        $entityRegistrant->phone_number = '1234567890';
        $entityRegistrant->game_type = 'SOME';
        $entityRegistrant->level_of_play = 'LEVEL';
        $entityRegistrant->state = 'CALIFORNIA';
        $entityRegistrant->address_first_line = 'An Address 1234';
        $entityRegistrant->county = 'A county';

        $entityCoach = new Coach;
        $entityCoach->certifications = 'Certification 1, certification 2, cert 3...';
        $entityCoach->years_of_experience = '5';

        try  {
            $entityRegistrant->save();
            $entityRegistrant->coach()->save($entityCoach);
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a player couldn't be created if the years_of_experience field is null
    */
    public function testShouldFailIfTheRequiredFieldYearsOfExperienceIsNull() {

        $entityRegistrant = new Registrant;
        $entityRegistrant->type = 'COACH' ;
        $entityRegistrant->first_name = 'Some name';
        $entityRegistrant->middle_name = 'Middle name';
        $entityRegistrant->last_name = 'Last Name';
        $entityRegistrant->email = 'mail@mail.com';
        $entityRegistrant->gender = 'Male';
        $entityRegistrant->city = 'California';
        $entityRegistrant->zip_code = '234141234123';
        $entityRegistrant->birth_date = '11/27/1984';
        $entityRegistrant->phone_number = '1234567890';
        $entityRegistrant->game_type = 'SOME';
        $entityRegistrant->level_of_play = 'LEVEL';
        $entityRegistrant->state = 'CALIFORNIA';
        $entityRegistrant->address_first_line = 'An Address 1234';
        $entityRegistrant->county = 'A county';

        $entityCoach = new Coach;
        $entityCoach->certifications = 'Certification 1, certification 2, cert 3...';
        $entityCoach->roles = 'Role 1, role 2, role 3...';

        try  {
            $entityRegistrant->save();
            $entityRegistrant->coach()->save($entityCoach);
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

}