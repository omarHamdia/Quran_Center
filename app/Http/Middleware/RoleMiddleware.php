<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // غير مسجل دخول
        if (! auth()->check()) {
            abort(403);
        }

        $userRole = auth()->user()->role;

      
        foreach ($roles as $role) {
            if (
                $userRole instanceof UserRole && $userRole->value === $role
                || is_string($userRole) && $userRole === $role
            ) {
                return $next($request);
            }
        }

        abort(403);
    }
}
