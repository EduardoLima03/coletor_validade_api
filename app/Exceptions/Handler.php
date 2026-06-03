<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [];

    protected $dontReport = [];

    protected $dontFlash = [
        "current_password",
        "password",
        "password_confirmation",
    ];

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is("api/*")) {
            return response()->json(["message" => "Unauthenticated."], 401);
        }

        return redirect()->guest(route("admin.login.form"));
    }

    public function register()
    {
        $this->reportable(function (Throwable $e) {});
    }
}
