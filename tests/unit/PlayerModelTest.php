<?php

namespace Tests\Unit;

use Mockery;
use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Models\Player;
use App\Models\Registrant;

class PlayerModelTest extends \TestCase {
    use DatabaseMigrations;
  
    /**
    * Should test that an ussf_id is generated on create
    */
    public function testShouldGenerateAnUsafbIdAfterSaving() {

        $entityRegistrant = new Registrant;
        $entityRegistrant->type = 'PLAYER' ;
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

        $entityPlayer = new Player;
        $entityPlayer->grade = 'K-12';
        $entityPlayer->height = '5 foot 10 inches';
        $entityPlayer->graduation_year = '2018';
        $entityPlayer->instagram = '@instagram';
        $entityPlayer->sports = 'sport1, sport2, sport3, ...';
        $entityPlayer->twitter = '@twitter';
        $entityPlayer->weight = '14 pounds';
        $entityPlayer->years_at_sport = '5';

        $entityRegistrant->save();
        $entityRegistrant->player()->save($entityPlayer);

        $this->assertTrue(!is_null($entityPlayer->usadfb_id));
    }

    /**
    * Should test that a player couldn't be created without it's parent
    */
    public function testShouldFailIfPlayerIsCreatedWithoutARegistrant() {
        $entity = new Player;
        $entity->grade = 'K-12';
        $entity->height = '5 foot 10 inches';
        $entity->graduation_year = '2018';
        $entity->instagram = '@instagram';
        $entity->sports = 'sport1, sport2, sport3, ...';
        $entity->twitter = '@twitter';
        $entity->weight = '14 pounds';
        $entity->years_at_sport = '5';

        try  {
            $entity->save();
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a player couldn't be created if the grade field is null
    */
    public function testShouldFailIfTheRequiredFieldGradeIsNull() {

        $entityRegistrant = new Registrant;
        $entityRegistrant->type = 'PLAYER' ;
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

        // Required field grade null 
        $entityPlayer = new Player;
        $entityPlayer->height = '5 foot 10 inches';
        $entityPlayer->graduation_year = '2018';
        $entityPlayer->instagram = '@instagram';
        $entityPlayer->sports = 'sport1, sport2, sport3, ...';
        $entityPlayer->twitter = '@twitter';
        $entityPlayer->weight = '14 pounds';
        $entityPlayer->years_at_sport = '5';


        try  {
            $entityRegistrant->save();
            $entityRegistrant->player()->save($entityPlayer);
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a player couldn't be created if the height field is null
    */
    public function testShouldFailIfTheRequiredFieldHeightIsNull() {

        $entityRegistrant = new Registrant;
        $entityRegistrant->type = 'PLAYER' ;
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

        // Required field grade null 
        $entityPlayer = new Player;
        $entityPlayer->grade = 'K-12';
        $entityPlayer->graduation_year = '2018';
        $entityPlayer->instagram = '@instagram';
        $entityPlayer->sports = 'sport1, sport2, sport3, ...';
        $entityPlayer->twitter = '@twitter';
        $entityPlayer->weight = '14 pounds';
        $entityPlayer->years_at_sport = '5';

        try  {
            $entityRegistrant->save();
            $entityRegistrant->player()->save($entityPlayer);
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a player couldn't be created if the graduation_year field is null
    */
    public function testShouldFailIfTheRequiredFieldGraduationYearIsNull() {

        $entityRegistrant = new Registrant;
        $entityRegistrant->type = 'PLAYER' ;
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

        // Required field grade null 
        $entityPlayer = new Player;
        $entityPlayer->grade = 'K-12';
        $entityPlayer->height = '5 foot 10 inches';
        $entityPlayer->instagram = '@instagram';
        $entityPlayer->sports = 'sport1, sport2, sport3, ...';
        $entityPlayer->twitter = '@twitter';
        $entityPlayer->weight = '14 pounds';
        $entityPlayer->years_at_sport = '5';

        try  {
            $entityRegistrant->save();
            $entityRegistrant->player()->save($entityPlayer);
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a player couldn't be created if the sports field is null
    */
    public function testShouldFailIfTheRequiredFieldSportsIsNull() {

        $entityRegistrant = new Registrant;
        $entityRegistrant->type = 'PLAYER' ;
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

        // Required field grade null 
        $entityPlayer = new Player;
        $entityPlayer->grade = 'K-12';
        $entityPlayer->height = '5 foot 10 inches';
        $entityPlayer->graduation_year = '2018';
        $entityPlayer->instagram = '@instagram';
        $entityPlayer->twitter = '@twitter';
        $entityPlayer->weight = '14 pounds';
        $entityPlayer->years_at_sport = '5';


        try  {
            $entityRegistrant->save();
            $entityRegistrant->player()->save($entityPlayer);
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a player couldn't be created if the weight field is null
    */
    public function testShouldFailIfTheRequiredFieldWeightIsNull() {

        $entityRegistrant = new Registrant;
        $entityRegistrant->type = 'PLAYER' ;
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

        // Required field grade null 
        $entityPlayer = new Player;
        $entityPlayer->grade = 'K-12';
        $entityPlayer->height = '5 foot 10 inches';
        $entityPlayer->graduation_year = '2018';
        $entityPlayer->instagram = '@instagram';
        $entityPlayer->sports = 'sport1, sport2, sport3, ...';
        $entityPlayer->twitter = '@twitter';
        $entityPlayer->years_at_sport = '5';

        try  {
            $entityRegistrant->save();
            $entityRegistrant->player()->save($entityPlayer);
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a player couldn't be created if the years_at_sport field is null
    */
    public function testShouldFailIfTheRequiredFieldYearsAtSportIsNull() {

        $entityRegistrant = new Registrant;
        $entityRegistrant->type = 'PLAYER' ;
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

        // Required field grade null 
        $entityPlayer = new Player;
        $entityPlayer->grade = 'K-12';
        $entityPlayer->height = '5 foot 10 inches';
        $entityPlayer->graduation_year = '2018';
        $entityPlayer->instagram = '@instagram';
        $entityPlayer->sports = 'sport1, sport2, sport3, ...';
        $entityPlayer->twitter = '@twitter';
        $entityPlayer->weight = '14 pounds';

        try  {
            $entityRegistrant->save();
            $entityRegistrant->player()->save($entityPlayer);
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a player can be created if requnired fields are null
    */
    public function testShouldCreateAPlayerIfNotRequiredFieldsAreNull() {

        $entityRegistrant = new Registrant;
        $entityRegistrant->type = 'PLAYER' ;
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

        // Null not required fields instagram and twitter 
        $entityPlayer = new Player;
        $entityPlayer->grade = 'K-12';
        $entityPlayer->height = '5 foot 10 inches';
        $entityPlayer->graduation_year = '2018';
        $entityPlayer->sports = 'sport1, sport2, sport3, ...';
        $entityPlayer->weight = '14 pounds';
        $entityPlayer->years_at_sport = '5';

        $entityRegistrant->save();
        $entityRegistrant->player()->save($entityPlayer);
        $this->assertTrue(!is_null($entityPlayer->usadfb_id));
    }

}