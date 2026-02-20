<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictToCampusIP
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = [
            '10.115.2.0/24',
            '10.10.64.0/21',
            '127.0.0.1'
        ];

        $clientIp = $request->ip();
        foreach ($allowedIps as $range) {
            if ($this->ipInRange($clientIp, $range)) {
                return $next($request);
            }
        }
        return redirect()->route('student.studentExams.index', ['status' => 'upcoming'])
            ->with('error', 'Access only allowed from campus network.');
    }

    private function ipInRange($ip, $range)
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        list($subnet, $mask) = explode('/', $range);

        return (ip2long($ip) & ~((1 << (32 - $mask)) - 1))
            == ip2long($subnet);
    }
}
