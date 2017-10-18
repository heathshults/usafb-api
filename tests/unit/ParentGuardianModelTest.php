<?php

namespace Tests\Unit;

use Mockery;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\Unit\Traits\CreateRelationshipEntities;
use App\Models\ParentGuardian;

class ParentGuardianModelTest extends \TestCase {
    use DatabaseMigrations;
    use CreateRelationshipEntities;

    /**
    * Should test that a parent is created ok
    */
    public function testShouldGenerateAParentSuccessfully() {
        $entityPlayerRegistration = $this->getPlayerRegistration();

        $entityParent = new ParentGuardian;
        $entityParent->mobile_phone = '123456789';
        $entityParent->email = 'parent1@email.com';
        $entityParent->first_name = 'parent1_first_name';
        $entityParent->last_name = 'parent1_last_name';
        $entityParent->home_phone = '123456';
        $entityParent->work_phone = '789010';

        $entityPlayerRegistration->parentsguardians()->save($entityParent);
        $this->assertTrue(!is_null($entityParent->id));
    }

    /**
    * Should test that a registration creation fail without source
    */
    public function testShouldFailIFAParentIsCreatedWithoutPlayerRegistration() {
        $this->expectException(\PDOException::class);

        $entityParent = new ParentGuardian;
        $entityParent->mobile_phone = '123456789';
        $entityParent->email = 'parent1@email.com';
        $entityParent->first_name = 'parent1_first_name';
        $entityParent->last_name = 'parent1_last_name';
        $entityParent->home_phone = '123456';
        $entityParent->work_phone = '789010';

        $entityParent->save();
    }

    /**
    * Should test that a parent can be created if required fields are null
    */
    public function testShouldCreateAParentIfNotRequiredFieldsAreNull() {
        $entityPlayerRegistration = $this->getPlayerRegistration();

        $entityParent = new ParentGuardian;

        $entityPlayerRegistration->parentsguardians()->save($entityParent);
        $this->assertTrue(!is_null($entityParent->id));
    }
}
