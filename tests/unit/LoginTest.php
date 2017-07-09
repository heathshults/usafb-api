<?php

namespace Tests\Unit;

use Mockery;

class LoginTest extends \TestCase
{
    /**
     * Test success on login endpoint
     *
     * @return void
     */
    public function testSuccessfulLogin()
    {
        $mockAuthService = Mockery::mock(\App\Http\Services\AuthService::class);
        $mockAuthService->shouldReceive('login')
            ->andReturn(
                [
                    'access_token' => '12345',
                    'expires_in'   => '0',
                    'scope'        => [],
                    'id_token'     => 'abc123',
                    'token_type'   => 'jwt',
                ]
            );
        $this->app->instance('Auth', $mockAuthService);

        $data = $mockAuthService->login('test1@gmail.com', 'test1');
        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('expires_in', $data);
        $this->assertArrayHasKey('scope', $data);
        $this->assertArrayHasKey('id_token', $data);
        $this->assertArrayHasKey('token_type', $data);
    }

    /**
     * Test failed on login endpoint when email is missing
     *
     * @return void
     */
    public function testMissingEmailLogin()
    {
        $this->json('post', '/rest/auth/login', ['password' => 'supersecure'])
            ->seeJson([
                'title' => 'Invalid Email',
            ]);

        $this->assertEquals(400, $this->response->status());
    }

    /**
     * Test failed on login endpoint when password is missing
     *
     * @return void
     */
    public function testMissingPasswordLogin()
    {
        $this->json('post', '/rest/auth/login', ['email' => 'test@test.com'])
            ->seeJson([
                'title' => 'Invalid Password',
            ]);

        $this->assertEquals(400, $this->response->status());
    }

    public function testMissingEmail()
    {
        $this->json('post', '/rest/auth/login', [])
            ->seeJson([
                'title' => 'Invalid Email',
            ])
            ->seeJson([
                'title' => 'Invalid Password',
            ]);

        $this->assertEquals(400, $this->response->status());
    }
}
