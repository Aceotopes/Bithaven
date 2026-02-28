<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminProfileController extends Controller
{
    public function update(Request $request)
    {
        $admin = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:admins,username,' . $admin->id,
            'photo' => 'nullable|image|max:2048',
            'remove_photo' => 'nullable'
        ]);

        $admin->name = $request->name;
        $admin->username = $request->username;

        // Remove existing photo
        if ($request->filled('remove_photo')) {
            if ($admin->photo_url) {
                Storage::disk('public')->delete($admin->photo_url);
            }
            $admin->photo_url = null;
        }

        // Upload new photo
        if ($request->hasFile('photo')) {
            if ($admin->photo_url) {
                Storage::disk('public')->delete($admin->photo_url);
            }

            $path = $request->file('photo')->store('admins', 'public');
            $admin->photo_url = $path;
        }

        $admin->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'admin' => $admin
        ]);
    }

    public function changePassword(Request $request)
    {
        $admin = $request->user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $admin->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.'
            ], 422);
        }

        $admin->password = Hash::make($request->new_password);
        $admin->save();

        return response()->json([
            'message' => 'Password updated successfully.'
        ]);
    }
}