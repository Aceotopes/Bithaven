<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminManagementController extends Controller
{
    //List all admins
    public function index()
    {
        return response()->json(
            Admin::select('id', 'username', 'name', 'role', 'status', 'created_at')
                ->orderBy('created_at')
                ->get()
        );
    }

    // create new admin
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:admins,username',
            'name' => 'required|string',
            'password' => 'required|string|min:6',
            'role' => 'required|in:ADMIN,SUPER_ADMIN',
        ]);

        $admin = Admin::create([
            'username' => $request->username,
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'ACTIVE',
        ]);

        return response()->json($admin, 201);
    }

    // update admin
    public function update(Request $request, Admin $admin)
    {
        $request->validate([
            'name' => 'sometimes|string',
            'password' => 'sometimes|string|min:6',
            'role' => 'sometimes|in:ADMIN,SUPER_ADMIN',
            'status' => 'sometimes|in:ACTIVE,INACTIVE',
        ]);

        if (
            $admin->role === 'SUPER_ADMIN' &&
            $request->role === 'ADMIN' &&
            Admin::where('role', 'SUPER_ADMIN')->count() <= 1
        ) {
            return response()->json([
                'message' => 'At least one SUPER_ADMIN must exist'
            ], 422);
        }

        if ($request->has('name')) {
            $admin->name = $request->name;
        }

        if ($request->has('password')) {
            $admin->password = Hash::make($request->password);
        }

        if ($request->has('role')) {
            $admin->role = $request->role;
        }

        if ($request->has('status')) {
            $admin->status = $request->status;
        }

        $admin->save();
        return response()->json($admin);
    }

    // delete admin
    public function destroy(Admin $admin)
    {
        if (
            $admin->role === 'SUPER_ADMIN' &&
            Admin::where('role', 'SUPER_ADMIN')->count() <= 1
        ) {
            return response()->json([
                'message' => 'Cannot delete the last SUPER_ADMIN'
            ], 422);
        }

        $admin->delete();
        return response()->json([
            'message' => 'Admin deleted successfully'
        ]);
    }
}
