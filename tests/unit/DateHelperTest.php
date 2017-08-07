<?php

namespace Tests\Unit;

use Mockery;
use App\Helpers\DateHelper;

class DateHelperTest extends \TestCase
{

	public function testIsLoginActiveBeforeKeyExpire() 
	{
		$this->assertTrue(DateHelper::isDateActive('2017-07-11T19:04:10Z', 20, '2017-07-11T19:04:10Z'));
	}

	public function testIsLoginActiveAfterKeyExpired() 
	{
		$this->assertFalse(DateHelper::isDateActive('2017-07-11T19:04:10Z', 20, '2017-07-11T19:08:10Z'));
	}

	public function testIsLoginActiveExactlyAtKeyExpired() 
	{
		$this->assertFalse(DateHelper::isDateActive('2017-07-11T19:04:10Z', 20, '2017-07-11T19:04:30Z'));
	}

	public function testIsLoginActiveOneSecondBeforeKeyExpired() 
	{
		$this->assertTrue(DateHelper::isDateActive('2017-07-11T19:04:10Z', 20, '2017-07-11T19:04:29Z'));
	}

	public function testIsLoginActiveAtTimeOfCreation()
	{
		$this->assertTrue(DateHelper::isDateActive('2017-07-11T19:04:10Z', 20, '2017-07-11T19:04:10Z'));
	}

	public function testIsLoginActiveNotOverridingTimeReference()
	{
		$now = date('Y-m-d\TH:i:s\Z');
		$this->assertTrue(DateHelper::isDateActive($now, 20));
	}

}

