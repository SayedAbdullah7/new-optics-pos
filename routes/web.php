<?php

use Illuminate\Support\Facades\Route;
use App\Models\Tenant;

/*
|--------------------------------------------------------------------------
| Central Domain Routes (stancl/tenancy)
|--------------------------------------------------------------------------
| These routes are only registered on central_domains (e.g. localhost, 127.0.0.1).
| Tenant app routes live in routes/tenant.php and are loaded by TenancyServiceProvider.
*/

foreach (config('tenancy.central_domains', ['127.0.0.1', 'localhost']) as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', function () {
            $tenant = Tenant::query()->with('domains')->first();
            if ($tenant && $tenant->domains->isNotEmpty()) {
                $host = $tenant->domains->first()->domain;
                return redirect()->away(request()->getScheme() . '://' . $host . '/');
            }
            return response('<h1>Multi-tenant POS</h1><p>No tenants yet. Create tenants and domains to access the app.</p>', 200, ['Content-Type' => 'text/html']);
        });
    });
}
