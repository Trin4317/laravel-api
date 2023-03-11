<?php

namespace App\Exceptions;

use Throwable;
use App\Traits\ApiResponser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    use ApiResponser;

    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // check Illuminate\Foundation\Exceptions\Handler::prepareException to see Exception -> Rendering relations
        $this->renderable(function (AccessDeniedHttpException $e, Request $request) {
            return $this->errorResponse('Action not allowed.', 403);
        });

        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            return $this->errorResponse('Record not found.', 404);
        });

        $this->renderable(function (MethodNotAllowedHttpException $e, Request $request) {
            return $this->errorResponse('Method not allowed.', 405);
        });

        $this->renderable(function (Throwable $e, Request $request) {
            Log::error($e->getMessage());
            return $this->errorResponse('Unexpected error.', 500);
        });
    }

    // Override unauthenticated method
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->shouldReturnJson($request, $exception)
                    ? $this->errorResponse('Unauthenticated.', 401)
                    : redirect()->guest($exception->redirectTo() ?? route('login'));
    }
}
