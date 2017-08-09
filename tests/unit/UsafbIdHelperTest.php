<?php

namespace Tests\Unit;

use Mockery;
use App\Helpers\UsafbIdHelper;

class UsafbIdHelperTest extends \TestCase
{

    public function testRandomGeneratedIdsMatchOriginal()
	{
        for ($i = 1; $i <= 1000; $i++) {

            // Given: A record index
            $recordNoOrig = rand(UsafbIdHelper::USAFID_MIN, UsafbIdHelper::USAFID_MAX);

            // When: A USAFB_ID is generated
            $usafbId = UsafbIdHelper::getId($recordNoOrig);
            $isValid = UsafbIdHelper::isValidId($usafbId);

            // Then: USAFB_ID is all caps.
            $this->assertEquals(strcmp($usafbId, strtoupper($usafbId)), 0);

            // Then: USAFB_ID is 7 characters long.
            $this->assertEquals(strlen($usafbId), 7);

            // Then: USAFB_ID contains no vowles.
            $this->assertEquals(preg_match("/^[^AEIOU]*$/", $usafbId), 1);

            // Then: USAFB_ID has valid lihn encoded check digit.
            $this->assertTrue(UsafbIdHelper::isValidId($usafbId));

            // Then: USAFB_ID converts back to the original record number.
            $recordNo = UsafbIdHelper::getRecordNo($usafbId);
            $this->assertEquals($recordNoOrig, $recordNo);

        }
	}

    public function testBadIsInvalid()
    {
        $this->assertFalse(UsafbIdHelper::isValidId('ABDEHFC'));
    }
}
