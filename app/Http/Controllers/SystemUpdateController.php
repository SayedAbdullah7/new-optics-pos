<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SystemUpdateController extends Controller
{
    /**
     * Run system updates and migrations manually on all tenant databases.
     */
    public function update()
    {
        $messages = [];
        $tenantDatabases = $this->getTenantDatabaseNames();

        foreach ($tenantDatabases as $databaseName) {
            $this->switchToTenantDatabase($databaseName);
            $messages[] = "--- Tenant DB: {$databaseName} ---";
            $messages = array_merge($messages, $this->runUpdatesOnCurrentConnection($databaseName));
        }

        return response()->json([
            'status' => true,
            'messages' => $messages
        ]);
    }

    /**
     * Get list of tenant database names. If multi-tenancy is configured, returns all; otherwise current only.
     */
    protected function getTenantDatabaseNames(): array
    {
        $databases = array_values(config('tenancy.databases', []));
        if (! empty($databases)) {
            return $databases;
        }
        return [config('database.connections.mysql.database', 'laravel')];
    }

    /**
     * Switch default mysql connection to the given tenant database.
     */
    protected function switchToTenantDatabase(string $databaseName): void
    {
        config(['database.connections.mysql.database' => $databaseName]);
        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    /**
     * Run all schema/artisan updates on the current DB connection. Returns messages prefixed for the tenant.
     */
    protected function runUpdatesOnCurrentConnection(string $databaseName): array
    {
        $prefix = "[{$databaseName}] ";
        $messages = [];

        // 0. Migrate Laratrust to Spatie Permission tables (if needed)
        foreach ($this->migrateLaratrustToSpatiePermissionTables() as $msg) {
            $messages[] = $prefix . $msg;
        }

        // 1. Check and create inventory_ledger table
        if (!Schema::hasTable('inventory_ledger')) {
            try {
                Schema::create('inventory_ledger', function (Blueprint $table) {
                    $table->id();
                    $table->string('stockable_type');              // Product or Lens
                    $table->unsignedBigInteger('stockable_id');
                    $table->enum('type', [
                        'purchase', 'sale', 'purchase_return', 'sale_return', 'adjustment'
                    ]);
                    $table->integer('quantity');                    // +/- (positive=in, negative=out)
                    $table->decimal('unit_cost', 15, 4);           // cost per unit at this transaction
                    $table->decimal('total_cost', 15, 4);          // quantity * unit_cost
                    $table->string('reference_type')->nullable();   // Bill, Invoice, etc.
                    $table->unsignedBigInteger('reference_id')->nullable();
                    $table->unsignedBigInteger('user_id')->nullable();
                    $table->text('description')->nullable();
                    $table->timestamps();

                    $table->index(['stockable_type', 'stockable_id']);
                    $table->index(['reference_type', 'reference_id']);
                    $table->index('type');
                });
                $messages[] = $prefix . 'Created table: inventory_ledger';
            } catch (\Exception $e) {
                $messages[] = $prefix . 'Error creating inventory_ledger table: ' . $e->getMessage();
            }
        } else {
            $messages[] = $prefix . 'Table inventory_ledger already exists.';
        }

        // 2. Add cost_price to invoice_items and invoice_lenses
        if (Schema::hasTable('invoice_items') && !Schema::hasColumn('invoice_items', 'cost_price')) {
            try {
                Schema::table('invoice_items', function (Blueprint $table) {
                    $table->decimal('cost_price', 15, 4)->default(0)->after('price');
                });
                $messages[] = $prefix . 'Added cost_price column to invoice_items';
            } catch (\Exception $e) {
                $messages[] = $prefix . 'Error adding cost_price to invoice_items: ' . $e->getMessage();
            }
        } else {
            if (Schema::hasColumn('invoice_items', 'cost_price')) {
                $messages[] = $prefix . 'Column cost_price already exists in invoice_items.';
            } else {
                $messages[] = $prefix . 'Table invoice_items does not exist, skipping cost_price column.';
            }
        }

        if (Schema::hasTable('invoice_lenses') && !Schema::hasColumn('invoice_lenses', 'cost_price')) {
            try {
                Schema::table('invoice_lenses', function (Blueprint $table) {
                    $table->decimal('cost_price', 15, 4)->default(0)->after('price');
                });
                $messages[] = $prefix . 'Added cost_price column to invoice_lenses';
            } catch (\Exception $e) {
                $messages[] = $prefix . 'Error adding cost_price to invoice_lenses: ' . $e->getMessage();
            }
        } else {
            if (Schema::hasColumn('invoice_lenses', 'cost_price')) {
                $messages[] = $prefix . 'Column cost_price already exists in invoice_lenses.';
            } else {
                $messages[] = $prefix . 'Table invoice_lenses does not exist, skipping cost_price column.';
            }
        }

        // 3. Add weighted_cost to products and lenses
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'weighted_cost')) {
            try {
                Schema::table('products', function (Blueprint $table) {
                    $table->decimal('weighted_cost', 15, 4)->default(0)->after('purchase_price');
                });
                $messages[] = $prefix . 'Added weighted_cost column to products';
            } catch (\Exception $e) {
                $messages[] = $prefix . 'Error adding weighted_cost to products: ' . $e->getMessage();
            }
        } else {
            if (Schema::hasColumn('products', 'weighted_cost')) {
                $messages[] = $prefix . 'Column weighted_cost already exists in products.';
            } else {
                $messages[] = $prefix . 'Table products does not exist, skipping weighted_cost column.';
            }
        }

        if (Schema::hasTable('lenses') && !Schema::hasColumn('lenses', 'weighted_cost')) {
            try {
                Schema::table('lenses', function (Blueprint $table) {
                    $table->decimal('weighted_cost', 15, 4)->default(0)->after('purchase_price');
                });
                $messages[] = $prefix . 'Added weighted_cost column to lenses';
            } catch (\Exception $e) {
                $messages[] = $prefix . 'Error adding weighted_cost to lenses: ' . $e->getMessage();
            }
        } else {
            if (Schema::hasColumn('lenses', 'weighted_cost')) {
                $messages[] = $prefix . 'Column weighted_cost already exists in lenses.';
            } else {
                $messages[] = $prefix . 'Table lenses does not exist, skipping weighted_cost column.';
            }
        }

        // 4. Check and create bill_lenses table
        if (!Schema::hasTable('bill_lenses')) {
            try {
                Schema::create('bill_lenses', function (Blueprint $table) {
                    $table->id();

                    // Use unsignedInteger for compatibility with older Laravel increments() tables
                    // If your tables use bigIncrements(), change this to foreignId() or unsignedBigInteger()
                    $table->unsignedInteger('bill_id');
                    $table->foreign('bill_id')->references('id')->on('bills')->onDelete('cascade');

                    // Assuming lenses is also unsignedInteger, if not, try unsignedBigInteger
                    $table->unsignedInteger('lens_id');
                    $table->foreign('lens_id')->references('id')->on('lenses')->onDelete('cascade');

                    $table->string('name')->nullable();
                    $table->integer('quantity')->default(1);
                    $table->decimal('price', 15, 2)->default(0);
                    $table->decimal('total', 15, 2)->default(0);
                    $table->timestamps();
                });
                $messages[] = $prefix . 'Created table: bill_lenses';
            } catch (\Exception $e) {
                // Retry with BigInteger if Integer fails (fallback strategy)
                try {
                     Schema::create('bill_lenses', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('bill_id')->constrained()->onDelete('cascade');
                        $table->foreignId('lens_id')->constrained('lenses')->onDelete('cascade');
                        $table->string('name')->nullable();
                        $table->integer('quantity')->default(1);
                        $table->decimal('price', 15, 2)->default(0);
                        $table->decimal('total', 15, 2)->default(0);
                        $table->timestamps();
                    });
                    $messages[] = $prefix . 'Created table: bill_lenses (using BigInt)';
                } catch (\Exception $e2) {
                     $messages[] = $prefix . 'Error creating bill_lenses (Int): ' . $e->getMessage();
                     $messages[] = $prefix . 'Error creating bill_lenses (BigInt): ' . $e2->getMessage();
                }
            }
        } else {
            $messages[] = $prefix . 'Table bill_lenses already exists.';
        }

        // 5. Sync Bill Stock (Historical Data)
        // We run this to ensure old bills have their stock mutations created if missing
        try {
            Artisan::call('stock:sync-bills');
            $output = Artisan::output();
            $messages[] = $prefix . 'Stock Sync Output: ' . trim($output);
        } catch (\Exception $e) {
            $messages[] = $prefix . 'Error syncing stock: ' . $e->getMessage();
        }

        return $messages;
    }

    /**
     * Migrate from Laratrust to Spatie Laravel Permission structure.
     * - Adds guard_name to existing roles & permissions.
     * - Creates model_has_roles, model_has_permissions, role_has_permissions.
     * - Migrates data from role_user, permission_user, permission_role.
     * - Drops old Laratrust pivot tables.
     *
     * @return array<string> Messages about what was done.
     */
    protected function migrateLaratrustToSpatiePermissionTables(): array
    {
        $messages = [];
        $guard = 'web';

        try {
            // 1. Add guard_name to roles (Spatie requires it)
            if (Schema::hasTable('roles') && !Schema::hasColumn('roles', 'guard_name')) {
                Schema::table('roles', function (Blueprint $table) use ($guard) {
                    $table->string('guard_name', 125)->default($guard)->after('name');
                });
                DB::table('roles')->whereNull('guard_name')->update(['guard_name' => $guard]);
                Schema::table('roles', function (Blueprint $table) {
                    $table->dropUnique(['name']);
                    $table->unique(['name', 'guard_name']);
                });
                $messages[] = 'Added guard_name to roles and updated unique key.';
            }

            // 2. Add guard_name to permissions
            if (Schema::hasTable('permissions') && !Schema::hasColumn('permissions', 'guard_name')) {
                Schema::table('permissions', function (Blueprint $table) use ($guard) {
                    $table->string('guard_name', 125)->default($guard)->after('name');
                });
                DB::table('permissions')->whereNull('guard_name')->update(['guard_name' => $guard]);
                Schema::table('permissions', function (Blueprint $table) {
                    $table->dropUnique(['name']);
                    $table->unique(['name', 'guard_name']);
                });
                $messages[] = 'Added guard_name to permissions and updated unique key.';
            }

            // 3. Create Spatie pivot tables
            if (Schema::hasTable('roles') && Schema::hasTable('permissions') && !Schema::hasTable('role_has_permissions')) {
                Schema::create('role_has_permissions', function (Blueprint $table) {
                    $table->unsignedInteger('permission_id');
                    $table->unsignedInteger('role_id');
                    $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
                    $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                    $table->primary(['permission_id', 'role_id']);
                });
                $messages[] = 'Created table: role_has_permissions';
            }

            if (Schema::hasTable('roles') && !Schema::hasTable('model_has_roles')) {
                Schema::create('model_has_roles', function (Blueprint $table) {
                    $table->unsignedInteger('role_id');
                    $table->string('model_type');
                    $table->unsignedBigInteger('model_id');
                    $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                    $table->index(['model_id', 'model_type']);
                    $table->primary(['role_id', 'model_id', 'model_type']);
                });
                $messages[] = 'Created table: model_has_roles';
            }

            if (Schema::hasTable('permissions') && !Schema::hasTable('model_has_permissions')) {
                Schema::create('model_has_permissions', function (Blueprint $table) {
                    $table->unsignedInteger('permission_id');
                    $table->string('model_type');
                    $table->unsignedBigInteger('model_id');
                    $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
                    $table->index(['model_id', 'model_type']);
                    $table->primary(['permission_id', 'model_id', 'model_type']);
                });
                $messages[] = 'Created table: model_has_permissions';
            }

            // 4. Migrate data: permission_role -> role_has_permissions
            if (Schema::hasTable('permission_role') && Schema::hasTable('role_has_permissions')) {
                $rows = DB::table('permission_role')->get();
                foreach ($rows as $row) {
                    DB::table('role_has_permissions')->insertOrIgnore([
                        'permission_id' => $row->permission_id,
                        'role_id' => $row->role_id,
                    ]);
                }
                if ($rows->isNotEmpty()) {
                    $messages[] = 'Migrated permission_role data to role_has_permissions.';
                }
            }

            // 5. Migrate data: role_user -> model_has_roles
            if (Schema::hasTable('role_user') && Schema::hasTable('model_has_roles')) {
                $rows = DB::table('role_user')->get();
                foreach ($rows as $row) {
                    DB::table('model_has_roles')->insertOrIgnore([
                        'role_id' => $row->role_id,
                        'model_type' => $row->user_type ?? \App\Models\User::class,
                        'model_id' => $row->user_id,
                    ]);
                }
                if ($rows->isNotEmpty()) {
                    $messages[] = 'Migrated role_user data to model_has_roles.';
                }
            }

            // 6. Migrate data: permission_user -> model_has_permissions
            if (Schema::hasTable('permission_user') && Schema::hasTable('model_has_permissions')) {
                $rows = DB::table('permission_user')->get();
                foreach ($rows as $row) {
                    DB::table('model_has_permissions')->insertOrIgnore([
                        'permission_id' => $row->permission_id,
                        'model_type' => $row->user_type ?? \App\Models\User::class,
                        'model_id' => $row->user_id,
                    ]);
                }
                if ($rows->isNotEmpty()) {
                    $messages[] = 'Migrated permission_user data to model_has_permissions.';
                }
            }

            // 7. Drop old Laratrust pivot tables
            if (Schema::hasTable('permission_role')) {
                Schema::dropIfExists('permission_role');
                $messages[] = 'Dropped table: permission_role';
            }
            if (Schema::hasTable('role_user')) {
                Schema::dropIfExists('role_user');
                $messages[] = 'Dropped table: role_user';
            }
            if (Schema::hasTable('permission_user')) {
                Schema::dropIfExists('permission_user');
                $messages[] = 'Dropped table: permission_user';
            }

            if (empty($messages)) {
                $messages[] = 'Laratrust→Spatie: Nothing to do (already migrated or tables missing).';
            }
        } catch (\Exception $e) {
            $messages[] = 'Laratrust→Spatie migration error: ' . $e->getMessage();
        }

        return $messages;
    }
}
