<?php

namespace Tests\Helpers;

use Mockery;
use App\Models\UserLog;
use App\Models\Enums\LogEvent;

class UserLogMockHelper
{
    /**
     * User log data
     *
     * @return array
     */
    static function userLogData()
    {
        $response = self::userLogResponse();

        $userLog = new UserLog();
        $userLog->old_value = $response['old_value'];
        $userLog->new_value = $response['new_value'];
        $userLog->user_id = $response['user'];
        $userLog->data_field = $response['data_field'];
        $userLog->created_by = $response['created_by'];
        $userLog->created_at = $response['created_at'];
        $userLog->event_type = $response['action'];

        return $userLog;
    }

    /**
     * User log response
     *
     * @return array
     */
    static function userLogResponse()
    {
        return [
            'old_value' => 'test',
            'new_value' => 'test1',
            'user' => 'auth0|123',
            'data_field' => 'first_name',
            'created_by' => 'auth0|456',
            'created_at' => '2017-07-24 10:00:00',
            'action' => LogEvent::UPDATE
        ];
    }

    /**
     * Mock DiffEngine
     *
     * @param boolean $modified Determine if field was modified
     *
     * @return Mock
     */
    static function mockDiffEngine($modified = true)
    {
        $mockDiffEngineItem = Mockery::mock(Pitpit\Component\Diff::class);
        $mockDiffEngineItem
            ->shouldReceive('getIdentifier')
            ->andReturn(
                'name'
            )->shouldReceive('isModified')
            ->andReturn(
                $modified
            )->shouldReceive('getOld')
            ->andReturn(
                'foo'
            )->shouldReceive('getNew')
            ->andReturn(
                $modified ? 'bar' : 'foo'
            );

        $mockDiffEngine = Mockery::mock(Pitpit\Component\Diff\DiffEngine::class);

        $mockDiffEngine
            ->shouldReceive('compare')
            ->andReturn(
                [
                    $mockDiffEngineItem
                ]
            );
        return $mockDiffEngine;
    }

    /**
     * Mock UserLogService
     *
     * @return void
     */
    static function mockService()
    {
        $userLogService = Mockery::mock(App\Http\Services\UserLogService::class);
        $userLogService
            ->shouldReceive('create')
            ->andReturn(
                []
            );

        return $userLogService;
    }


}
