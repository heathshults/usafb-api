<?php

namespace Tests\Unit\Services;

use Tests\Helpers\AuthMockHelper;
use App\Http\Services\UserLogService;
use Tests\Helpers\UserLogMockHelper;
use App\Models\Enums\LogEvent;

class UserLogServiceTest extends \TestCase
{
    protected static $service;

    public static function setUpBeforeClass()
    {
        self::$service = new UserLogService();
    }

    /**
     * Test valid log response
     *
     * @return void
     */
    public function testSuccessfullLogResponse()
    {
        $userId = 'auth0|134';
        $action = LogEvent::CREATE;
        $admin = AuthMockHelper::user();
        $log = self::$service->generatelogInfo(
            $userId,
            $action,
            $admin
        );

        $this->assertEquals($log['user_id'], $userId);
        $this->assertEquals($log['event_type'], $action);
        $this->assertNull($log['data_field']);
        $this->assertNull($log['old_value']);
        $this->assertNull($log['new_value']);
        $this->assertEquals($log['created_by'], $admin->getFirstName()." ".$admin->getLastName());
        $this->assertEquals($log['created_at'], date('Y/m/d H:i:s'));
    }

    /**
     * Test valid log list response
     *
     * @return void
     */
    public function testSuccessfullLogListResponse()
    {
        self::$service->setDiffEngine(UserLogMockHelper::mockDiffEngine());

        $admin = AuthMockHelper::user();
        $old = AuthMockHelper::user();
        $new = AuthMockHelper::user();

        $logs = self::$service->generateLogsFromUpdate(
            $admin,
            $old,
            $new
        );
        $log = $logs[0];

        $this->assertEquals($log['user_id'], $new->getId());
        $this->assertEquals($log['event_type'], LogEvent::UPDATE);
        $this->assertEquals($log['data_field'], 'Name');
        $this->assertEquals($log['old_value'], 'foo');
        $this->assertEquals($log['new_value'], 'bar');
        $this->assertEquals($log['created_by'], $admin->getFirstName()." ".$admin->getLastName());
        $this->assertEquals($log['created_at'], date('Y/m/d H:i:s'));
    }

    /**
     * Test empty log list response when none user field was modified
     *
     * @return void
     */
    public function testEmptyLogListResponseWhenNonModified()
    {
        self::$service->setDiffEngine(UserLogMockHelper::mockDiffEngine(false));

        $admin = AuthMockHelper::user();
        $old = AuthMockHelper::user();
        $new = AuthMockHelper::user();

        $logs = self::$service->generateLogsFromUpdate(
            $admin,
            $old,
            $new
        );
        $this->assertEquals($logs, []);
    }
}
