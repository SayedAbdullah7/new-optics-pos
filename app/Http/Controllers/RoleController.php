<?php

namespace App\Http\Controllers;

use App\DataTables\RoleDataTable;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(RoleDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.roles.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            $parts = explode('-', $permission->name);
            return end($parts); // Group by the last part (module)
        });
        return view('pages.roles.form', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'         => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->where('guard_name', 'web')],
            'display_name' => 'required|string|max:255',
            'description'  => 'nullable|string|max:255',
            'permissions'  => 'nullable|array',
            'permissions.*'=> 'exists:permissions,name',
        ]);

        try {
            DB::beginTransaction();

            $role = Role::create($request->only(['name', 'display_name', 'description']) + ['guard_name' => 'web']);

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Role created successfully.',
                'data' => $role
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to create role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role): View
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            $parts = explode('-', $permission->name);
            return end($parts);
        });
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('pages.roles.form', ['model' => $role, 'permissions' => $permissions, 'rolePermissions' => $rolePermissions]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $request->validate([
            'name'         => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role->id)],
            'display_name' => 'required|string|max:255',
            'description'  => 'nullable|string|max:255',
            'permissions'  => 'nullable|array',
            'permissions.*'=> 'exists:permissions,name',
        ]);

        try {
            DB::beginTransaction();

            $role->update($request->only(['name', 'display_name', 'description']));

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            } else {
                $role->syncPermissions([]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Role updated successfully.',
                'data' => $role
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to update role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): JsonResponse
    {
        if ($role->users()->count() > 0) {
            return response()->json([
                'status' => false,
                'msg' => 'Cannot delete role with assigned users.'
            ], 403);
        }

        try {
            $role->delete();
            return response()->json([
                'status' => true,
                'msg' => 'Role deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed to delete role: ' . $e->getMessage()
            ], 500);
        }
    }
}
