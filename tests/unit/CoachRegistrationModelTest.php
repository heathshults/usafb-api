<?php

namespace Tests\Unit;

use Mockery;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\Unit\Traits\CreateRelationshipEntities;
use App\Models\CoachRegistration;

class CoachRegistrationTest extends \TestCase {
    use DatabaseMigrations;
    use CreateRelationshipEntities;

    /**
    * Should test that creates a coach registration successfully
    */
    public function testShouldGenerateACoachRegistrationSuccessfully() {

        $entityRegistration = $this->getRegistration();

        $entityCoachRegistration = new CoachRegistration;
        $entityCoachRegistration->certifications = 'Certification 1, certification 2, cert 3...';
        $entityCoachRegistration->roles = 'Role 1, role 2, role 3...';
        $entityCoachRegistration->years_of_experience = '5';

        $entityRegistration->coachRegistration()->save($entityCoachRegistration);

        $this->assertTrue(!is_null($entityCoachRegistration->id));
    }

    /**
    * Should test that a coach registration couldn't be created without it's parent
    */
    public function testShouldFailIfCoachRegistrationIsCreatedWithoutARegistration() {

        $entityCoachRegistration = new CoachRegistration;
        $entityCoachRegistration->certifications = 'Certification 1, certification 2, cert 3...';
        $entityCoachRegistration->roles = 'Role 1, role 2, role 3...';
        $entityCoachRegistration->years_of_experience = '5';

        try  {
            $entityCoachRegistration->save();
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a coach can be created if the certification field is null
    */
    public function testShouldNotFailIfTheRequiredFieldCertificationIsNull() {

        $entityRegistration = $this->getRegistration();

        $entityCoachRegistration = new CoachRegistration;
        $entityCoachRegistration->roles = 'Role 1, role 2, role 3...';
        $entityCoachRegistration->years_of_experience = '5';

        try  {
            $entityRegistration->coachRegistration()->save($entityCoachRegistration);
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a player couldn't be created if the roles field is null
    */
    public function testShouldFailIfTheRequiredFieldRolesIsNull() {

        $entityRegistration = $this->getRegistration();

        $entityCoachRegistration = new CoachRegistration;
        $entityCoachRegistration->certifications = 'Certification 1, certification 2, cert 3...';
        $entityCoachRegistration->years_of_experience = '5';

        try  {
            $entityRegistration->coachRegistration()->save($entityCoachRegistration);
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }

    /**
    * Should test that a player couldn't be created if the years_of_experience field is null
    */
    public function testShouldFailIfTheRequiredFieldYearsOfExperienceIsNull() {

        $entityRegistration = $this->getRegistration();

        $entityCoachRegistration = new CoachRegistration;
        $entityCoachRegistration->certifications = 'Certification 1, certification 2, cert 3...';
        $entityCoachRegistration->roles = 'Role 1, role 2, role 3...';

        try  {
            $entityRegistration->coachRegistration()->save($entityCoachRegistration);
        } catch (\PDOException $e) {
            $this->assertEquals($e->getCode(), '23502');
        }
    }
}