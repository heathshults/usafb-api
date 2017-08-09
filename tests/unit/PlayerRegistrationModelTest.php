<?php

namespace Tests\Unit;

use Mockery;
use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Models\PlayerRegistration;
use App\Models\Registrant;
use App\Models\Registration;
use App\Models\Source;

class PlayerRegistrationTest extends \TestCase {
    use DatabaseMigrations;

    private function getSource() {
        $entity = new Source;
        $entity->name = 'Source Name Test' ;
        $entity->api_key = 'ThisIsMyTestApiKey';

        $entity->save();

        return $entity;
    }

    private function getRegistrant() {
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

        return $entity;
    }

    private function getRegistration() {
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
        //Registrant fields
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

        return $entity;
    }
  
    /**
    * Should test that creates a player registration successfully
    */
    public function testShouldGenerateAPlayerRegistrationSuccessfully() {

        $entityRegistration = $this->getRegistration();

        $entityPlayerRegistration = new PlayerRegistration;
        $entityPlayerRegistration->positions = 'positions...';
        $entityPlayerRegistration->team_age_group = '2017';
        $entityPlayerRegistration->school_name = 'school name...';
        $entityPlayerRegistration->grade = 'K-12';
        $entityPlayerRegistration->height = '5.3';
        $entityPlayerRegistration->graduation_year = '2018';
        $entityPlayerRegistration->instagram = '@instagram';
        $entityPlayerRegistration->sports = 'my sports...';
        $entityPlayerRegistration->twitter = '@twitter';
        $entityPlayerRegistration->weight = '10';
        $entityPlayerRegistration->years_at_sport = '3';

        $entityRegistration->playerRegistration()->save($entityPlayerRegistration);

        $this->assertTrue(!is_null($entityPlayerRegistration->id));
    }

    /**
    * Should test that a player registration couldn't be created without it's parent
    */
    public function testShouldFailIfPlayerRegistrationIsCreatedWithoutARegistration() {
        $entityRegistration = $this->getRegistration();

        $entityPlayerRegistration = new PlayerRegistration;
        $entityPlayerRegistration->positions = 'positions...';
        $entityPlayerRegistration->team_age_group = '2017';
        $entityPlayerRegistration->school_name = 'school name...';
        $entityPlayerRegistration->grade = 'K-12';
        $entityPlayerRegistration->height = '5.3';
        $entityPlayerRegistration->graduation_year = '2018';
        $entityPlayerRegistration->instagram = '@instagram';
        $entityPlayerRegistration->sports = 'my sports...';
        $entityPlayerRegistration->twitter = '@twitter';
        $entityPlayerRegistration->weight = '10';
        $entityPlayerRegistration->years_at_sport = '3';

        try  {
            $entityRegistration->save();
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a player reg couldn't be created if the grade field is null
    */
    public function testShouldFailIfTheRequiredFieldGradeIsNull() {

        $entityRegistration = $this->getRegistration();

        $entityPlayerRegistration = new PlayerRegistration;
        $entityPlayerRegistration->positions = 'positions...';
        $entityPlayerRegistration->team_age_group = '2017';
        $entityPlayerRegistration->school_name = 'school name...';
        $entityPlayerRegistration->height = '5.3';
        $entityPlayerRegistration->graduation_year = '2018';
        $entityPlayerRegistration->instagram = '@instagram';
        $entityPlayerRegistration->sports = 'my sports...';
        $entityPlayerRegistration->twitter = '@twitter';
        $entityPlayerRegistration->weight = '10';
        $entityPlayerRegistration->years_at_sport = '3';

        try  {
            $entityRegistration->playerRegistration()->save($entityPlayerRegistration);
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a player couldn't be created if the height field is null
    */
    public function testShouldFailIfTheRequiredFieldHeightIsNull() {

        $entityRegistration = $this->getRegistration();

        $entityPlayerRegistration = new PlayerRegistration;
        $entityPlayerRegistration->positions = 'positions...';
        $entityPlayerRegistration->team_age_group = '2017';
        $entityPlayerRegistration->school_name = 'school name...';
        $entityPlayerRegistration->grade = 'K-12';
        $entityPlayerRegistration->graduation_year = '2018';
        $entityPlayerRegistration->instagram = '@instagram';
        $entityPlayerRegistration->sports = 'my sports...';
        $entityPlayerRegistration->twitter = '@twitter';
        $entityPlayerRegistration->weight = '10';
        $entityPlayerRegistration->years_at_sport = '3';

        try  {
            $entityRegistration->playerRegistration()->save($entityPlayerRegistration);
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a player couldn't be created if the graduation_year field is null
    */
    public function testShouldFailIfTheRequiredFieldGraduationYearIsNull() {

        $entityRegistration = $this->getRegistration();

        $entityPlayerRegistration = new PlayerRegistration;
        $entityPlayerRegistration->positions = 'positions...';
        $entityPlayerRegistration->team_age_group = '2017';
        $entityPlayerRegistration->school_name = 'school name...';
        $entityPlayerRegistration->grade = 'K-12';
        $entityPlayerRegistration->height = '5.3';
        $entityPlayerRegistration->instagram = '@instagram';
        $entityPlayerRegistration->sports = 'my sports...';
        $entityPlayerRegistration->twitter = '@twitter';
        $entityPlayerRegistration->weight = '10';
        $entityPlayerRegistration->years_at_sport = '3';

        try  {
            $entityRegistration->playerRegistration()->save($entityPlayerRegistration);
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a player couldn't be created if the sports field is null
    */
    public function testShouldFailIfTheRequiredFieldSportsIsNull() {

        $entityRegistration = $this->getRegistration();

        $entityPlayerRegistration = new PlayerRegistration;
        $entityPlayerRegistration->positions = 'positions...';
        $entityPlayerRegistration->team_age_group = '2017';
        $entityPlayerRegistration->school_name = 'school name...';
        $entityPlayerRegistration->grade = 'K-12';
        $entityPlayerRegistration->height = '5.3';
        $entityPlayerRegistration->graduation_year = '2018';
        $entityPlayerRegistration->instagram = '@instagram';
        $entityPlayerRegistration->twitter = '@twitter';
        $entityPlayerRegistration->weight = '10';
        $entityPlayerRegistration->years_at_sport = '3';

        try  {
            $entityRegistration->playerRegistration()->save($entityPlayerRegistration);
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a player couldn't be created if the weight field is null
    */
    public function testShouldFailIfTheRequiredFieldWeightIsNull() {

        $entityRegistration = $this->getRegistration();

        $entityPlayerRegistration = new PlayerRegistration;
        $entityPlayerRegistration->positions = 'positions...';
        $entityPlayerRegistration->team_age_group = '2017';
        $entityPlayerRegistration->school_name = 'school name...';
        $entityPlayerRegistration->grade = 'K-12';
        $entityPlayerRegistration->height = '5.3';
        $entityPlayerRegistration->graduation_year = '2018';
        $entityPlayerRegistration->instagram = '@instagram';
        $entityPlayerRegistration->sports = 'my sports...';
        $entityPlayerRegistration->twitter = '@twitter';
        $entityPlayerRegistration->years_at_sport = '3';

        try  {
            $entityRegistration->playerRegistration()->save($entityPlayerRegistration);
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a player couldn't be created if the years_at_sport field is null
    */
    public function testShouldFailIfTheRequiredFieldYearsAtSportIsNull() {

        $entityRegistration = $this->getRegistration();

        $entityPlayerRegistration = new PlayerRegistration;
        $entityPlayerRegistration->positions = 'positions...';
        $entityPlayerRegistration->team_age_group = '2017';
        $entityPlayerRegistration->school_name = 'school name...';
        $entityPlayerRegistration->grade = 'K-12';
        $entityPlayerRegistration->height = '5.3';
        $entityPlayerRegistration->graduation_year = '2018';
        $entityPlayerRegistration->instagram = '@instagram';
        $entityPlayerRegistration->sports = 'my sports...';
        $entityPlayerRegistration->twitter = '@twitter';
        $entityPlayerRegistration->weight = '10';

        try  {
            $entityRegistration->playerRegistration()->save($entityPlayerRegistration);
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a player can be created if requnired fields are null
    */
    public function testShouldCreateAPlayerIfNotRequiredFieldsAreNull() {

        $entityRegistration = $this->getRegistration();

        $entityPlayerRegistration = new PlayerRegistration;
        $entityPlayerRegistration->grade = 'K-12';
        $entityPlayerRegistration->height = '5.3';
        $entityPlayerRegistration->graduation_year = '2018';
        $entityPlayerRegistration->sports = 'my sports...';
        $entityPlayerRegistration->weight = '10';
        $entityPlayerRegistration->years_at_sport = '3';

        $entityRegistration->playerRegistration()->save($entityPlayerRegistration);

        $this->assertTrue(!is_null($entityPlayerRegistration->id));
    }

}