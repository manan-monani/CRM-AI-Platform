<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Explicitly check the admin guard
        if (! Auth::guard('admin')->check() || (! Auth::guard('admin')->user()->isSuperAdmin() && ! Auth::guard('admin')->user()->isAdmin())) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
