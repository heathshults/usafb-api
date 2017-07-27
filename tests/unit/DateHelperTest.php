<?php

namespace Tests\Unit;

use Mockery;
use App\Helpers\DateHelper;

class DateHelperTest extends \TestCase
{

	public function testIsLoginActiveBeforeKeyExpire() 
	{
		$this->assertEquals(DateHelper::isDateActive('2017-07-11T19:04:10Z', 20, '2017-07-11T19:04:10Z'), true);
	}

	public function testIsLoginActiveAfterKeyExpired() 
	{
		$this->assertEquals(DateHelper::isDateActive('2017-07-11T19:04:10Z', 20, '2017-07-11T19:08:10Z'), false);
	}

	public function testIsLoginActiveExactlyAtKeyExpired() 
	{
		$this->assertEquals(DateHelper::isDateActive('2017-07-11T19:04:10Z', 20, '2017-07-11T19:04:30Z'), false);
	}

	public function testIsLoginActiveOneSecondBeforeKeyExpired() 
	{
		$this->assertEquals(DateHelper::isDateActive('2017-07-11T19:04:10Z', 20, '2017-07-11T19:04:29Z'), true);
	}

	public function testIsLoginActiveAtTimeOfCreation()
	{
		$this->assertEquals(DateHelper::isDateActive('2017-07-11T19:04:10Z', 20, '2017-07-11T19:04:10Z'), true);
	}

	public function testIsLoginActiveNotOverridingTimeReference()
	{
		$now = date('Y-m-d\TH:i:s\Z');
		$this->assertEquals(DateHelper::isDateActive($now, 20), true);
	}

}

