<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
       if (! $user) {
            // Simpan URL saat ini supaya bisa redirect setelah login
            session(['url.intended' => $request->fullUrl()]);
            return redirect()->route('login');
        }
        if (!$user || !$user->roles()->whereIn('name', $roles)->exists()) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
