<?php

namespace App\Http\Controllers;

use App\DataTables\UserDataTable;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(UserDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.users.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $roles = Role::all();
        return view('pages.users.form', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users',
            'password'   => 'required|string|min:8|confirmed',
            'roles'      => 'nullable|array',
            'roles.*'    => 'exists:roles,id',
        ]);

        try {
            DB::beginTransaction();

            $userData = $request->except(['password', 'password_confirmation', 'roles']);
            $userData['name'] = $request->first_name . ' ' . $request->last_name;
            $userData['password'] = Hash::make($request->password);

            $user = User::create($userData);

            if ($request->has('roles')) {
                $user->syncRoles($request->roles);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'User created successfully.',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        return view('pages.users.form', ['model' => $user, 'roles' => $roles, 'userRoles' => $userRoles]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password'   => 'nullable|string|min:8|confirmed',
            'roles'      => 'nullable|array',
            'roles.*'    => 'exists:roles,id',
        ]);

        try {
            DB::beginTransaction();

            $userData = $request->except(['password', 'password_confirmation', 'roles']);
            $userData['name'] = $request->first_name . ' ' . $request->last_name;

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            if ($request->has('roles')) {
                $user->syncRoles($request->roles);
            } else {
                $user->syncRoles([]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'User updated successfully.',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'status' => false,
                'msg' => 'You cannot delete yourself.'
            ], 403);
        }

        try {
            $user->delete();
            return response()->json([
                'status' => true,
                'msg' => 'User deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }
}
