<?php

namespace Tests\Unit\Helpers;

use Mockery;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Exceptions\InternalException;
use App\Traits\JsonResponseTrait;

class JsonResponseTraitTest extends \TestCase
{
    use JsonResponseTrait;

    public function testIsValidationException()
    {
        $this->assertTrue($this->isValidationException(
            Mockery::mock(\Illuminate\Validation\ValidationException::class)));
    }

    public function testIsNotValidationException()
    {
        $this->assertFalse($this->isValidationException(
            Mockery::mock(\App\Exceptions\InternalException::class)));
    }

    public function testIsModelNotFoundException()
    {
        $this->assertTrue($this->isModelNotFoundException(
            Mockery::mock(\Illuminate\Database\Eloquent\ModelNotFoundException::class)));
    }

    public function testIsNotModelNotFoundExceptionn()
    {
        $this->assertFalse($this->isModelNotFoundException(
            Mockery::mock(\App\Exceptions\InternalException::class)));
    }

    public function testIsClientException()
    {
        $this->assertTrue($this->isClientException(
            Mockery::mock(\GuzzleHttp\Exception\ClientException::class)));
    }

    public function testIsNotClientException()
    {
        $this->assertFalse($this->isClientException(
            Mockery::mock(\App\Exceptions\InternalException::class)));
    }

    public function testIsUnauthorizedHttpException()
    {
        $this->assertTrue($this->isUnauthorizedException(
            Mockery::mock(\Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException::class)));
    }

    public function testIsNotUnauthorizedHttpException()
    {
        $this->assertFalse($this->isUnauthorizedException(
            Mockery::mock(\App\Exceptions\InternalException::class)));
    }

    public function testIsNotFoundException()
    {
        $this->assertTrue($this->isNotFoundException(
            Mockery::mock(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class)));
    }

    public function testIsNotNotFoundException()
    {
        $this->assertFalse($this->isNotFoundException(
            Mockery::mock(\App\Exceptions\InternalException::class)));
    }

    public function testIsAccessDeniedHttpException()
    {
        $this->assertTrue($this->isAccessDeniedHttpException(
            Mockery::mock(\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException::class)));
    }

    public function testIsNotAccessDeniedHttpException()
    {
        $this->assertFalse($this->isAccessDeniedHttpException(
            Mockery::mock(\App\Exceptions\InternalException::class)));
    }

    public function testIsConflictHttpException()
    {
        $this->assertTrue($this->isConflictHttpException(
            Mockery::mock(\Symfony\Component\HttpKernel\Exception\ConflictHttpException::class)));
    }

    public function testIsNotConflictHttpException()
    {
        $this->assertFalse($this->isConflictHttpException(
            Mockery::mock(\App\Exceptions\InternalException::class)));
    }

    public function testIsBadRequestException()
    {
        $this->assertTrue($this->isBadRequestException(
            Mockery::mock(\Symfony\Component\HttpKernel\Exception\BadRequestHttpException::class)));
    }

    public function testIsNotBadRequestException()
    {
        $this->assertFalse($this->isBadRequestException(
            Mockery::mock(\App\Exceptions\InternalException::class)));
    }

    public function testIsInternalException()
    {
        $this->assertTrue($this->isInternalException(
            Mockery::mock(\App\Exceptions\InternalException::class)));
    }

    public function testIsNotInternalException()
    {
        $this->assertFalse($this->isInternalException(
            Mockery::mock(\Symfony\Component\HttpKernel\Exception\BadRequestHttpException::class)));
    }

    public function testJsonResponseForStringMessage()
    {
        $response = $this->jsonResponse("some error");
        $this->assertEquals(json_encode($response->getData()), json_encode([
            'errors' => [
                ['error' => 'some error']
            ]
        ]));
    }

    public function testJsonResponseDefaultCode()
    {
        $response = $this->jsonResponse("some error");
        $this->assertEquals($response->getStatusCode(), 404);
    }

    public function testJsonResponseForNonStringMessage()
    {
        $response = $this->jsonResponse(["response" => "some error"]);
        $this->assertEquals(json_encode($response->getData()), json_encode([
            'errors' => [
                'response' => 'some error'
            ]
        ]));
    }

    public function testJsonResponseProvidedCode()
    {
        $response = $this->jsonResponse("some error", 403);
        $this->assertEquals($response->getStatusCode(), 403);
    }

    public function testGetJsonErrorObjectsResponse()
    {
        $response = $this->getJsonErrorObjects([
            "title" => ['Title not valid'],
            "email" => ['Email not valid']
        ]);
        $this->assertEquals(
            $response,
            [[
                "code" => 'invalid_attribute',
                "title" => 'Invalid Title',
                "error" => 'Title not valid'
            ],
            [
                "code" => 'invalid_attribute',
                "title" => 'Invalid Email',
                "error" => 'Email not valid'
            ]]
        );
    }

    public function testGetJsonErrorObjectsEmptyResponse()
    {
        $response = $this->getJsonErrorObjects([]);
        $this->assertEquals(
            $response,
            []
        );
    }
}
