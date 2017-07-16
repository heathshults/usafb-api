<?php

namespace Tests\Unit;

use Mockery;
use App\Http\Services\AuthService;
use \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App\Models\Enums\Role;

class UserTest extends \TestCase
{

    /**
     * Test successfull get token from header
     *
     * @return void
     */
    public function testSuccefullTokenHeader()
    {
        $service = new AuthService();
        $headers = [
            'Authorization' => ['Bearer token123']
        ];
        $this->assertEquals('token123', $service->getHeaderToken($headers));
    }

    /**
     * Test failed get token from header when missing token
     *
     * @return void
     */
    public function testMissingTokenHeader()
    {
        $service = new AuthService();
        $headers = [];
        $this->expectException(UnauthorizedHttpException::class);
        $service->getHeaderToken($headers);
    }

    /**
     * Test sucessfull get user info
     *
     * @return void
     */
    public function testSuccefullGetUser()
    {
        $mockAuth = Mockery::mock(\Auth0\SDK\API\Authentication::class);
        $mockAuth->shouldReceive('userinfo')
            ->andReturn(
                [
                    "sub" => "auth0|123",
                    "name" => "test@gmail.com",
                    "nickname" => "test",
                    "picture" => "https =>//s.gravatar.com/avatar/123.png",
                    "updated_at" => "2017-07-06T21 =>10 =>27.449Z",
                    "email" => "test@gmail.com",
                    "email_verified" => true,
                    "http://ussfb.com/metadata" => array(
                        "firstName" => "test",
                        "lastName" => "test",
                        "city" => "Frisco",
                        "state" => "TX",
                        "postalCode" => "70000",
                        'roles' => [Role::USAFB_ADMIN]
                    )
                ]
            );
        $service = new AuthService();
        $service->setAuthentication($mockAuth);
        $headers = [
            'Authorization' => 'Bearer token123'
        ];
        $data = $service->getUser($headers);
        $this->assertEquals('auth0|123', $data['sub']);
        $this->assertEquals('test@gmail.com', $data['name']);
        $this->assertEquals('test', $data['nickname']);
        $this->assertEquals('https =>//s.gravatar.com/avatar/123.png', $data['picture']);
        $this->assertEquals('2017-07-06T21 =>10 =>27.449Z', $data['updated_at']);
        $this->assertEquals('test@gmail.com', $data['email']);
        $this->assertEquals(true, $data['email_verified']);
        $this->assertEquals(
            array(
                "firstName" => "test",
                "lastName" => "test",
                "city" => "Frisco",
                "state" => "TX",
                "postalCode" => "70000",
                "roles" => [Role::USAFB_ADMIN]
            ),
            $data['http://ussfb.com/metadata']
        );
    }
}
