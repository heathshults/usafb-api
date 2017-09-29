<?php

namespace Tests\Unit;

use Mockery;
use Tests\Helpers\AuthMockHelper;
use App\Http\Services\AuthService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResetPasswordTest extends \TestCase
{
    /**
     * Test success on forgot password endpoint
     *
     * @return void
     */
    public function testSuccessfulResetPassword()
    {
        $service = new AuthService();
        $service->setAuthentication(AuthMockHelper::authenticationMock());
        $service->setManagement(AuthMockHelper::managementMock([], [AuthMockHelper::userResponse()]));

        $response = $service->resetPassword('test1@gmail.com');
        $this->assertEquals($response->getData()->message, 'Email sent');
    }

    /**
     * Test exception thrown when user with email provided does not exists
     *
     * @return void
     */
    public function testFailedResetPasswordUnexistingUser()
    {
        $service = new AuthService();
        $service->setAuthentication(AuthMockHelper::authenticationMock());
        $service->setManagement(AuthMockHelper::managementMock());

        $this->expectException(NotFoundHttpException::class);
        $response = $service->resetPassword('test1@gmail.com');

    }

    /**
     * Test failed on forgot password endpoint when email is missing
     *
     * @return void
     */
    public function testMissingEmail()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());
        $this->app->instance('App\Http\Middleware\Authenticate', AuthMockHelper::authenticateMiddlewareMock());

        $this->json('post', '/reset-password', [])
            ->seeJson([
                'title' => 'Invalid Email',
            ])
            ->seeStatusCode(400);
    }

    /**
     * Test failed on login endpoint when email is blank
     *
     * @return void
     */
    public function testBlankEmail()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());
        $this->app->instance('App\Http\Middleware\Authenticate', AuthMockHelper::authenticateMiddlewareMock());

        $this->json('post', '/reset-password', ['email' => ''])
            ->seeJson([
                'title' => 'Invalid Email',
            ])
            ->seeStatusCode(400);
    }

}
