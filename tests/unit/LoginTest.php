<?php

namespace Tests\Unit;

use Mockery;
use Tests\Helpers\AuthMockHelper;

class LoginTest extends \TestCase
{
    /**
     * Test success on login endpoint
     *
     * @return void
     */
    public function testSuccessfulLogin()
    {
        $mockAuthService = AuthMockHelper::authServiceMock();
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
        $this->json('post', '/login', ['password' => 'supersecure'])
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
        $this->json('post', '/login', ['email' => 'test@test.com'])
            ->seeJson([
                'title' => 'Invalid Password',
            ]);

        $this->assertEquals(400, $this->response->status());
    }

    /**
     * Test failed on login endpoint when password and email are missing
     *
     * @return void
     */
    public function testMissingEmailAndPassword()
    {
        $this->json('post', '/login', [])
            ->seeJson([
                'title' => 'Invalid Email',
            ])
            ->seeJson([
                'title' => 'Invalid Password',
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
        $this->json('post', '/login', ['password' => 'supersecure', 'email' => ''])
            ->seeJson([
                'title' => 'Invalid Email',
            ]);

        $this->assertEquals(400, $this->response->status());
    }

    /**
     * Test failed on login endpoint when password is blank
     *
     * @return void
     */
    public function testBlankPassword()
    {
        $this->json('post', '/login', ['email' => 'test@test.com', 'password' => ''])
            ->seeJson([
                'title' => 'Invalid Password',
            ]);

        $this->assertEquals(400, $this->response->status());
    }

    /**
     * Test failed on login endpoint when password and email are blank
     *
     * @return void
     */
    public function testBlankEmailAndPassword()
    {
        $this->json('post', '/login', ['email' => '', 'password' => ''])
            ->seeJson([
                'title' => 'Invalid Email',
            ])
            ->seeJson([
                'title' => 'Invalid Password',
            ]);

        $this->assertEquals(400, $this->response->status());
    }
}
