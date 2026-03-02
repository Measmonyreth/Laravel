<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException; // ✅ Add this line
use Throwable;

class Handler extends ExceptionHandler
{
    // ... your existing code ...

    // ✅ Add this method at the bottom before closing }
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'message' => 'Invalid or expired token.',
        ], 401);
    }
}
