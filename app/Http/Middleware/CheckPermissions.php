<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Facades\Authorize;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissions
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (empty($permissions)) {
            return $next($request);
        }

        if (! Authorize::any($permissions)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
