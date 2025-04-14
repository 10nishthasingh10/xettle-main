<?php

namespace App\Exceptions;

use App\Helpers\ResponseHelper;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'code' => '0x0200',
                    'status' => 'UNAUTHORIZED',
                    'message' => 'Not authenticated'
                ], 401);
            }
        });
    }



    /**
     * render
     *
     * @param  mixed $request
     * @param  mixed $exception
     * @return void
     */
    public function render($request, Throwable $exception)
    {
        if (!$this->isHttpException($exception)) {
            if ($request->is('v1/*')) {
                //dd($exception->getMessage());
                if (str_contains($exception->getMessage(), CONNECTION_TIMEOUT_MSG_RESPONSE)) {
                    
                    return ResponseHelper::timeout(CONNECTION_TIMEOUT_MSG);
                }
                return ResponseHelper::swwrong("INTERNAL SERVER ERROR", [], 500);
            }
        }
        return parent::render($request, $exception);
    }
}
