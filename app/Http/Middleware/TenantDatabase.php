<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TenantDatabase
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $databases = config('tenancy.databases', []);
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];

        if (isset($databases[$subdomain])) {
            config([
                'database.connections.mysql.database' => $databases[$subdomain],
            ]);

            DB::purge('mysql');
            DB::reconnect('mysql');
        }

        return $next($request);
    }
}
