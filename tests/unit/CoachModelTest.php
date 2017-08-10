<?php

namespace Tests\Unit;

use Mockery;
use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Models\Coach;
use Tests\Unit\Traits\CreateRelationshipEntities;

class CoachModelTest extends \TestCase {
    use DatabaseMigrations;
    use CreateRelationshipEntities;
  
    /**
    * Should test that an ussf_id is generated on create
    */
    public function testShouldGenerateACoachSuccessfully() {

        $entityRegistrant = $this->getRegistrant();

        $entityCoach = new Coach;
        $entityCoach->certifications = 'Certification 1, certification 2, cert 3...';
        $entityCoach->roles = 'Role 1, role 2, role 3...';
        $entityCoach->years_of_experience = '5';

        $entityRegistrant->save();
        $entityRegistrant->coach()->save($entityCoach);

        $this->assertTrue(!is_null($entityCoach->id));
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

        $entityRegistrant = $this->getRegistrant();

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

        $entityRegistrant = $this->getRegistrant();

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

        $entityRegistrant = $this->getRegistrant();

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