<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant Databases (Multi-Tenancy)
    |--------------------------------------------------------------------------
    | Subdomain => database name. Used by TenantDatabase middleware and
    | SystemUpdateController to run migrations/updates on all tenants.
    |
    */

    'databases' => [
        'alasadiya-sky' => 'alasadiya_db',
        'abuhamad-sky'  => 'abuhamad_db',
        'demo'           => 'demo_db',
    ],

];
