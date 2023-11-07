<?php

namespace App\Exceptions;

use App\Http\Resources\CustomResponseResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return (new CustomResponseResource([
                'title' => 'Unauthenticated',
                'message' => 'You are not authenticated. Please log in.',
                'errorCode' => 401,
            ]))->response()->setStatusCode(401);
        }

        return redirect('/admin/login');
    }

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthorizationException) {
            return (new CustomResponseResource([
                'title' => 'Unauthorized',
                'message' => 'This action is unauthorized.',
                'errorCode' => 403,
            ]))->response()->setStatusCode(403);
        }

        return parent::render($request, $exception);
    }
}
