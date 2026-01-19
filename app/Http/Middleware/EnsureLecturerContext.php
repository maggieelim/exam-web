<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLecturerContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $context): Response
    {
        $user = auth()->user();
        if ($user->hasRole('lecturer')) {
            $lecturer = $user->lecturer;
            if (($context == 'pssk' && !$lecturer->canAccessPssk()) || ($context == 'pspd' && !$lecturer->canAccessPspd())) {
                abort('403', 'Anda tidak memiliki akses ke web ini');
            }
        }
        return $next($request);
    }
}
