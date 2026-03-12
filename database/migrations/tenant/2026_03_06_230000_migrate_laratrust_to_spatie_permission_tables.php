<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migrate from Laratrust to Spatie Laravel Permission structure.
     * - Adds guard_name to existing roles & permissions.
     * - Creates model_has_roles, model_has_permissions, role_has_permissions.
     * - Migrates data from role_user, permission_user, permission_role.
     * - Drops old Laratrust pivot tables.
     */
    public function up(): void
    {
        $guard = 'web';

        // 1. Add guard_name to roles (Spatie requires it)
        if (!Schema::hasColumn('roles', 'guard_name')) {
            Schema::table('roles', function (Blueprint $table) use ($guard) {
                $table->string('guard_name', 125)->default($guard)->after('name');
            });
            DB::table('roles')->whereNull('guard_name')->update(['guard_name' => $guard]);
            Schema::table('roles', function (Blueprint $table) {
                $table->dropUnique(['name']);
                $table->unique(['name', 'guard_name']);
            });
        }

        // 2. Add guard_name to permissions
        if (!Schema::hasColumn('permissions', 'guard_name')) {
            Schema::table('permissions', function (Blueprint $table) use ($guard) {
                $table->string('guard_name', 125)->default($guard)->after('name');
            });
            DB::table('permissions')->whereNull('guard_name')->update(['guard_name' => $guard]);
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropUnique(['name']);
                $table->unique(['name', 'guard_name']);
            });
        }

        // 3. Create Spatie pivot tables (using unsignedInteger to match existing roles/permissions id type)
        if (!Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', function (Blueprint $table) {
                $table->unsignedInteger('permission_id');
                $table->unsignedInteger('role_id');
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                $table->primary(['permission_id', 'role_id']);
            });
        }

        if (!Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function (Blueprint $table) {
                $table->unsignedInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                $table->index(['model_id', 'model_type']);
                $table->primary(['role_id', 'model_id', 'model_type']);
            });
        }

        if (!Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', function (Blueprint $table) {
                $table->unsignedInteger('permission_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
                $table->index(['model_id', 'model_type']);
                $table->primary(['permission_id', 'model_id', 'model_type']);
            });
        }

        // 4. Migrate data: permission_role -> role_has_permissions
        if (Schema::hasTable('permission_role')) {
            $rows = DB::table('permission_role')->get();
            foreach ($rows as $row) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $row->permission_id,
                    'role_id' => $row->role_id,
                ]);
            }
        }

        // 5. Migrate data: role_user -> model_has_roles (user_type -> model_type, user_id -> model_id)
        if (Schema::hasTable('role_user')) {
            $rows = DB::table('role_user')->get();
            foreach ($rows as $row) {
                DB::table('model_has_roles')->insertOrIgnore([
                    'role_id' => $row->role_id,
                    'model_type' => $row->user_type ?? \App\Models\User::class,
                    'model_id' => $row->user_id,
                ]);
            }
        }

        // 6. Migrate data: permission_user -> model_has_permissions
        if (Schema::hasTable('permission_user')) {
            $rows = DB::table('permission_user')->get();
            foreach ($rows as $row) {
                DB::table('model_has_permissions')->insertOrIgnore([
                    'permission_id' => $row->permission_id,
                    'model_type' => $row->user_type ?? \App\Models\User::class,
                    'model_id' => $row->user_id,
                ]);
            }
        }

        // 7. Drop old Laratrust pivot tables
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_user');
    }

    public function down(): void
    {
        // Recreate Laratrust pivot tables
        Schema::create('permission_role', function (Blueprint $table) {
            $table->unsignedInteger('permission_id');
            $table->unsignedInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
            $table->foreign('role_id')->references('id')->on('roles');
        });
        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('user_id');
            $table->string('user_type');
            $table->primary(['user_id', 'role_id', 'user_type']);
            $table->foreign('role_id')->references('id')->on('roles');
        });
        Schema::create('permission_user', function (Blueprint $table) {
            $table->unsignedInteger('permission_id');
            $table->unsignedInteger('user_id');
            $table->string('user_type');
            $table->primary(['user_id', 'permission_id', 'user_type']);
            $table->foreign('permission_id')->references('id')->on('permissions');
        });

        // Migrate data back (simplified: copy from Spatie tables)
        DB::table('role_has_permissions')->orderBy('role_id')->orderBy('permission_id')->get()->each(function ($row) {
            DB::table('permission_role')->insertOrIgnore(['permission_id' => $row->permission_id, 'role_id' => $row->role_id]);
        });
        DB::table('model_has_roles')->where('model_type', \App\Models\User::class)->get()->each(function ($row) {
            DB::table('role_user')->insertOrIgnore(['role_id' => $row->role_id, 'user_id' => $row->model_id, 'user_type' => $row->model_type]);
        });
        DB::table('model_has_permissions')->where('model_type', \App\Models\User::class)->get()->each(function ($row) {
            DB::table('permission_user')->insertOrIgnore(['permission_id' => $row->permission_id, 'user_id' => $row->model_id, 'user_type' => $row->model_type]);
        });

        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');

        // Remove guard_name and restore unique on name
        if (Schema::hasColumn('roles', 'guard_name')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropUnique(['name', 'guard_name']);
                $table->unique('name');
                $table->dropColumn('guard_name');
            });
        }
        if (Schema::hasColumn('permissions', 'guard_name')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropUnique(['name', 'guard_name']);
                $table->unique('name');
                $table->dropColumn('guard_name');
            });
        }
    }
};
