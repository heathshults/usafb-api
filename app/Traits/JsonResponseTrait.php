<?php

namespace App\Traits;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

trait JsonResponseTrait
{
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
            case $this->isModelNotFoundException($e):
                return $this->modelNotFound();
            default:
                return $this->badRequest();
        }
    }

    /**
     * Returns json response for generic bad request.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function badRequest($errors = [], $statusCode = 400)
    {
        return $this->jsonResponse(['errors' => $this->getJsonErrorObjects($errors)], $statusCode);
    }

    /**
     * Transform an array of errors into error response object
     *
     * @param array $errors
     * @return array
     */
    protected function getJsonErrorObjects($errors = [])
    {
        return array_map(function ($field, $error) {
            return [
                'code' => 'invalid_attribute',
                'title' => "Invalid " . ucwords($field),
                'error' => $error[0],
            ];
        }, array_keys($errors), $errors);
    }

    /**
     * Returns json response for Eloquent model not found exception.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function modelNotFound($message = 'Record not found', $statusCode = 404)
    {
        return $this->jsonResponse(['error' => $message], $statusCode);
    }

    /**
     * Returns json response.
     *
     * @param array|null $payload
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonResponse(array $payload = null, $statusCode = 404)
    {
        $payload = $payload ?: [];

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
}
