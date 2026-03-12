<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

/**
 * Register existing tenant databases with stancl/tenancy (one-time migration from custom tenancy).
 * Creates Tenant + Domain records and sets tenancy_db_name so no new DB is created.
 */
class RegisterExistingTenants extends Command
{
    protected $signature = 'tenancy:register-existing
                            {--subdomain= : Subdomain for the tenant (e.g. alasadiya-sky)}
                            {--database= : Database name (e.g. alasadiya_db)}
                            {--host=localhost : Host used for domain (e.g. localhost or your domain)}';

    protected $description = 'Register an existing tenant DB with stancl/tenancy (subdomain + database name). Run once per existing tenant.';

    public function handle(): int
    {
        $subdomain = $this->option('subdomain') ?? $this->ask('Subdomain (tenant id)', 'demo');
        $database = $this->option('database') ?? $this->ask('Database name', 'demo_db');
        $host = $this->option('host') ?: 'localhost';

        $tenantId = $subdomain;
        $domain = $subdomain . '.' . $host;

        if (Tenant::find($tenantId)) {
            $this->error("Tenant with id [{$tenantId}] already exists.");
            return self::FAILURE;
        }

        $tenant = new Tenant();
        $tenant->id = $tenantId;
        $tenant->setInternal('db_name', $database);
        $tenant->setInternal('create_database', false);
        $tenant->save();

        $tenant->domains()->create(['domain' => $domain]);

        $this->info("Tenant [{$tenantId}] registered with domain [{$domain}] and database [{$database}].");
        return self::SUCCESS;
    }
}
