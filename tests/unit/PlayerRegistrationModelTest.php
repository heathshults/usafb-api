<?php

namespace Tests\Unit;

use Mockery;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\Unit\Traits\CreateRelationshipEntities;
use App\Models\PlayerRegistration;

class PlayerRegistrationTest extends \TestCase {
    use DatabaseMigrations;
    use CreateRelationshipEntities;

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
        $this->expectException(\PDOException::class);

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

        $entityPlayerRegistration->save();
    }

    /**
    * Should test that a player reg couldn't be created if the grade field is null
    */
    public function testShouldFailIfTheRequiredFieldGradeIsNull() {
        $this->expectException(\PDOException::class);

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

        $entityRegistration->playerRegistration()->save($entityPlayerRegistration);
    }

    /**
    * Should test that a player couldn't be created if the height field is null
    */
    public function testShouldFailIfTheRequiredFieldHeightIsNull() {
        $this->expectException(\PDOException::class);

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

        $entityRegistration->playerRegistration()->save($entityPlayerRegistration);
    }

    /**
    * Should test that a player couldn't be created if the graduation_year field is null
    */
    public function testShouldFailIfTheRequiredFieldGraduationYearIsNull() {
        $this->expectException(\PDOException::class);

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

        $entityRegistration->playerRegistration()->save($entityPlayerRegistration);
    }

    /**
    * Should test that a player couldn't be created if the sports field is null
    */
    public function testShouldFailIfTheRequiredFieldSportsIsNull() {
        $this->expectException(\PDOException::class);

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

        $entityRegistration->playerRegistration()->save($entityPlayerRegistration);
    }

    /**
    * Should test that a player couldn't be created if the weight field is null
    */
    public function testShouldFailIfTheRequiredFieldWeightIsNull() {
        $this->expectException(\PDOException::class);

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

        $entityRegistration->playerRegistration()->save($entityPlayerRegistration);
    }

    /**
    * Should test that a player couldn't be created if the years_at_sport field is null
    */
    public function testShouldFailIfTheRequiredFieldYearsAtSportIsNull() {
        $this->expectException(\PDOException::class);

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

        $entityRegistration->playerRegistration()->save($entityPlayerRegistration);
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