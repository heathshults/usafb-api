<?php

namespace Tests\Unit\Transformers;

use Tests\Helpers\UserLogMockHelper;
use App\Transformers\UserLogTransformer;
use App\Models\Enums\LogEvent;


class UserLogTransformerTest extends \TestCase
{
    /**
     * Test success on transforming user log response from database
     * into the defined user log response
     *
     * @return void
     */
    public function testTransformResponse()
    {
        $log = UserLogMockHelper::userLogData();
        $transformer = new UserLogTransformer();

        $response = $transformer->transform($log);

        $this->assertEquals($response['old_value'], 'test');
        $this->assertEquals($response['new_value'], 'test1');
        $this->assertEquals($response['user'], 'auth0|123');
        $this->assertEquals($response['data_field'], 'first_name');
        $this->assertEquals($response['created_by'], 'auth0|456');
        $this->assertEquals($response['created_at'], '2017-07-24 10:00:00');
        $this->assertEquals($response['action'], LogEvent::UPDATE);
    }
}
