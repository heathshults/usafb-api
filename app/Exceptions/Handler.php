<?php

namespace App\Exceptions;

use Exception;
use App\Traits\JsonResponseTrait;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    use JsonResponseTrait;
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Render JSON Error Response
     *
     * @param Illuminate\Http\Request $request
     * @param Exception $e
     * @return Illuminate\Http\JsonResponse
     */
    public function render($request, Exception $e)
    {
        return $this->getJsonResponseForException($request, $e);
    }
}
