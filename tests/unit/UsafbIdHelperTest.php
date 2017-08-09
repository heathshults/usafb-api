<?php

namespace Tests\Unit;

use Mockery;
use App\Helpers\UsafbIdHelper;

class UsafbIdHelperTest extends \TestCase
{

    public function testRandomGeneratedIdsMatchOriginal()
	{
        for ($i = 1; $i <= 1000; $i++) {
            $recordNoOrig = rand(UsafbIdHelper::USAFID_MIN, UsafbIdHelper::USAFID_MAX);
            $usafbId = UsafbIdHelper::getId($recordNoOrig);
            $recordNo = UsafbIdHelper::getRecordNo($usafbId);
            $isValid = UsafbIdHelper::isValidId($usafbId);

            // Test USAFB_ID converts back to the original record number.
            $this->assertEquals($recordNoOrig, $recordNo);

            // Test USAFB_ID is all caps.
            $this->assertEquals(strcmp($usafbId, strtoupper($usafbId)), 0);

            // Test USAFB_ID contains no vowles.
            $this->assertEquals(preg_match("/^[^AEIOU]*$/", $usafbId), 1);

            // Test USAFB_ID is 7 characters.
            $this->assertEquals(strlen($usafbId), 7);

            // Test USAFB_ID is valid.
            $this->assertTrue(UsafbIdHelper::isValidId($usafbId));
        }
	}

    public function testBadIsInvalid()
    {
        $this->assertFalse(UsafbIdHelper::isValidId('ABDEHFC'));
    }
}
