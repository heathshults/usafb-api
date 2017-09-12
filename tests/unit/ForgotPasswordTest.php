<?php

namespace Tests\Unit;

use Mockery;

class ForgotPasswordTest extends \TestCase
{
    /**
     * Test success on forgot password endpoint
     *
     * @return void
     */
    public function testSuccessfulForgotPassword()
    {
        $mockAuthService = Mockery::mock(\App\Http\Services\AuthService::class);
        $mockAuthService->shouldReceive('forgotPassword')
            ->andReturn(
                [
                    'message' => 'Email sent'
                ]
            );
        $this->app->instance('Auth', $mockAuthService);

        $data = $mockAuthService->forgotPassword('test1@gmail.com');
        $this->assertEquals($data['message'], 'Email sent');
    }

    /**
     * Test failed on forgot password endpoint when email is missing
     *
     * @return void
     */
    public function testMissingEmailForgotPassword()
    {
        $this->json('post', '/forgot-password', [])
            ->seeJson([
                'title' => 'Invalid Email',
            ]);

        $this->assertEquals(400, $this->response->status());
    }

    /**
     * Test failed on login endpoint when email is blank
     *
     * @return void
     */
    public function testBlankEmail()
    {
        $this->json('post', '/forgot-password', ['email' => ''])
            ->seeJson([
                'title' => 'Invalid Email',
            ]);

        $this->assertEquals(400, $this->response->status());
    }

}
