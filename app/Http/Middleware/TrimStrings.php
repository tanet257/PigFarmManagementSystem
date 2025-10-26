<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array<int, string>
     */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Determine if the request has a URI that should be ignored.
     */
    protected function shouldSkip($request)
    {
        // Skip trimming for multipart form data (file uploads)
        if ($request->isMethod('post') && $request->header('content-type') && str_contains($request->header('content-type'), 'multipart/form-data')) {
            return true;
        }
        return false;
    }
}
