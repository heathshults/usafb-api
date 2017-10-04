<?php

namespace Tests\Unit\Transformers;

use Tests\Helpers\AuthMockHelper;
use App\Transformers\UserTransformer;
use App\Models\User;
use App\Models\Enums\Role;


class UserTransformerTest extends \TestCase
{
    /**
     * Test success on transforming user auth0 response
     * into the defined user response
     *
     * @return void
     */
    public function testTransformResponse()
    {

        $user = AuthMockHelper::user();
        $transformer = new UserTransformer();

        $response = $transformer->transform($user);

        $userResponse = AuthMockHelper::authUserResponse();
        $this->assertEquals($response['id'], $userResponse['sub']);
        $this->assertEquals($response['email'], $userResponse['email']);
        $this->assertEquals($response['nickname'], $userResponse['nickname']);
        $this->assertEquals($response['email_verified'], $userResponse['email_verified']);

        $metadata = $userResponse[getenv('AUTH_METADATA')];
        $this->assertEquals($response['phone_number'], $metadata['phone_number']);
        $this->assertEquals($response['first_name'], $metadata['first_name']);
        $this->assertEquals($response['last_name'], $metadata['last_name']);
        $this->assertEquals($response['roles'], $metadata['roles']);
        $this->assertEquals($response['city'], $metadata['city']);
        $this->assertEquals($response['country'], $metadata['country']);
        $this->assertEquals($response['state'], $metadata['state']);
        $this->assertEquals($response['postal_code'], $metadata['postal_code']);
        $this->assertEquals($response['created_by'], $metadata['created_by']);
        $this->assertEquals($response['updated_by'], $metadata['updated_by']);
    }

    /**
     * Test success on transforming user auth0 response
     * into the defined user response when fields are missing
     * Reproduces the default values
     *
     * @return void
     */
    public function testTransformResponseMissingFields()
    {
        $user = AuthMockHelper::user([], []);

        $transformer = new UserTransformer();
        $response = $transformer->transform($user);

        $this->assertNull($response['first_name']);
        $this->assertNull($response['last_name']);
        $this->assertNull($response['roles']);
        $this->assertNull($response['state']);
        $this->assertNull($response['city']);
        $this->assertNull($response['country']);
        $this->assertNull($response['postal_code']);
        $this->assertNull($response['phone_number']);
        $this->assertNull($response['email']);
        $this->assertNull($response['id']);
        $this->assertFalse($response['email_verified']);
        $this->assertNull($response['picture']);
        $this->assertEquals($response['status'], 'Enabled');
        $this->assertNull($response['created_at']);
        $this->assertNull($response['updated_at']);
        $this->assertFalse(isset($response['updated_by']));
        $this->assertFalse(isset($response['created_by']));
        $this->assertFalse(isset($response['nickname']));
    }
}
