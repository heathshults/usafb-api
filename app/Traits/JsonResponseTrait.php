<?php

namespace App\Traits;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Exceptions\InternalException;

trait JsonResponseTrait
{
    protected static $badRequestCode = 400;
    protected static $unauthorizedCode = 401;
    protected static $accessDeniedCode = 403;
    protected static $notFoundCode = 404;
    protected static $conflictCode = 409;
    protected static $internalServerErrorCode = 500;

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param Request $request
     * @param Exception $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForException(Request $request, Exception $e)
    {
        switch (true) {
            case $this->isValidationException($e):
                return $this->badRequest($e->getResponse()->getData(true));
            case $this->isBadRequestException($e):
                return $this->errorMessage($e->getMessage(), self::$badRequestCode);
            case $this->isModelNotFoundException($e):
            case $this->isNotFoundException($e):
                return $this->modelNotFound();
            case $this->isUnauthorizedException($e):
                return $this->errorMessage($e->getMessage(), self::$unauthorizedCode);
            case $this->isAccessDeniedHttpException($e):
                return $this->errorMessage($e->getMessage(), self::$accessDeniedCode);
            case $this->isConflictHttpException($e):
                return $this->errorMessage($e->getMessage(), self::$conflictCode);
            case $this->isClientException($e):
                return $this->clientExceptionMessage(
                    $e->getResponse(),
                    $e->getResponse()->getStatusCode()
                );
            case $this->isInternalException($e):
                return $this->errorMessage($e->getMessage(), self::$internalServerErrorCode);
            default:
                return $this->errorMessage("Bad Request", self::$badRequestCode);
        }
    }

    /**
     * Returns message for client exception response.
     *
     * @param string $error
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function clientExceptionMessage($response, $statusCode)
    {
        $error = json_decode($response->getBody()->__toString(), true);
        if (isset($error)) {
            if (isset($error['message'])) {
                return $this->errorMessage($error['message'], $statusCode);
            }
            if (isset($error['error_description'])) {
                return $this->errorMessage($error['error_description'], $statusCode);
            }
        }
        return $this->errorMessage("Bad Request", self::$badRequestCode);
    }

    /**
     * Returns json response for generic error message.
     *
     * @param string $error
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorMessage($message = "", $statusCode = "")
    {
        if ($statusCode === "") {
            $statusCode = self::$badRequestCode;
        }
        return $this->jsonResponse($message, $statusCode);
    }

    /**
     * Returns json response for generic bad request.
     *
     * @param string $errors
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function badRequest($errors = [], $statusCode = "")
    {
        if ($statusCode === "") {
            $statusCode = self::$badRequestCode;
        }
        return $this->jsonResponse($this->getJsonErrorObjects($errors), $statusCode);
    }

    /**
     * Transform an array of errors into error response object
     *
     * @param array $errors
     * @return array
     */
    protected function getJsonErrorObjects($errors = [])
    {
        return array_map(
            function ($field, $error) {
                return [
                    'code' => 'invalid_attribute',
                    'title' => "Invalid " . ucwords($field),
                    'error' => $error[0],
                ];
            },
            array_keys($errors),
            $errors
        );
    }

    /**
     * Returns json response for Eloquent model not found exception.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function modelNotFound($message = 'Record not found', $statusCode = "")
    {
        if ($statusCode === "") {
            $statusCode = self::$notFoundCode;
        }
        return $this->jsonResponse($message, $statusCode);
    }

    /**
     * Returns json response.
     *
     * @param array|null $payload
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonResponse($response = null, $statusCode = "")
    {
        if ($statusCode === "") {
            $statusCode = self::$notFoundCode;
        }
        
        $payload = [ 
            'data' => [   
                'error' => [
                    'message' => 'An error occurred'
                ]
            ] 
        ];

        if (is_string($response)) {
            $payload['data']['error']['message'] = $response;            
        } else {
            $payload['data']['error']['errors'] = $response;
        }
                
        return response()->json($payload, $statusCode);
    }

    /**
     * Determines if the given exception is a Validation exception from a controller
     *
     * @param Exception $e
     * @return boolean
     */
    protected function isValidationException(Exception $e)
    {
        return $e instanceof ValidationException;
    }

    /**
     * Determines if the given exception is an Eloquent model not found.
     *
     * @param Exception $e
     * @return bool
     */
    protected function isModelNotFoundException(Exception $e)
    {
        return $e instanceof ModelNotFoundException;
    }

    /**
     * Determines if the given exception is an Client exception from a controller.
     *
     * @param Exception $e
     * @return bool
     */
    protected function isClientException(Exception $e)
    {
        return $e instanceof ClientException;
    }

    /**
     * Determines if the given exception is an Unauthorized exception from a controller.
     *
     * @param Exception $e
     * @return bool
     */
    protected function isUnauthorizedException(Exception $e)
    {
        return $e instanceof UnauthorizedHttpException;
    }

    /**
     * Determines if the given exception is an Notfound exception from a controller.
     *
     * @param Exception $e
     * @return bool
     */
    protected function isNotFoundException(Exception $e)
    {
        return $e instanceof NotFoundHttpException;
    }

    /**
     * Determines if the given exception is an Access Denied exception from a controller.
     *
     * @param Exception $e
     * @return bool
     */
    protected function isAccessDeniedHttpException(Exception $e)
    {
        return $e instanceof AccessDeniedHttpException;
    }

    /**
     * Determines if the given exception is an Conflict exception from a controller.
     *
     * @param Exception $e
     * @return bool
     */
    protected function isConflictHttpException(Exception $e)
    {
        return $e instanceof ConflictHttpException;
    }

    /**
    * Determines if the given exception is a Bad Request exception from a controller.
    *
    * @param Exception $e
    * @return bool
    */
    protected function isBadRequestException(Exception $e)
    {
        return $e instanceof BadRequestHttpException;
    }

    /**
    * Determines if the given exception is a Internal Exception from a controller.
    *
    * @param Exception $e
    * @return bool
    */
    protected function isInternalException(Exception $e)
    {
        return $e instanceof InternalException;
    }
}
