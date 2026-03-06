<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TenantDatabase
{
    /**
     * Map subdomains to tenant databases.
     */
    protected array $databases = [
        'alasadiya-sky' => 'alasadiya_db',
        'abuhamad-sky'  => 'abuhamad_db',
        'demo'           => 'demo_db',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];

        if (isset($this->databases[$subdomain])) {
            config([
                'database.connections.mysql.database' => $this->databases[$subdomain],
            ]);

            DB::purge('mysql');
            DB::reconnect('mysql');
        }

        return $next($request);
    }
}
