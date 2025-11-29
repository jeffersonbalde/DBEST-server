<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PropertyCustodian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class PropertyCustodianAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $custodian = PropertyCustodian::where('email', $request->email)->first();

        if (!$custodian || !Hash::check($request->password, $custodian->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$custodian->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        $token = $custodian->createToken('property-custodian-token')->plainTextToken;

        return response()->json([
            'user' => $custodian,
            'token' => $token,
            'user_type' => 'property_custodian',
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|unique:property_custodians',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:property_custodians',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string',
            'position' => 'nullable|string',
        ]);

        $custodian = PropertyCustodian::create([
            'employee_id' => $request->employee_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'position' => $request->position,
        ]);

        $token = $custodian->createToken('property-custodian-token')->plainTextToken;

        return response()->json([
            'user' => $custodian,
            'token' => $token,
            'user_type' => 'property_custodian',
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\PropertyCustodian $custodian */
        $custodian = $request->user();

        $data = $request->validate([
            'username' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                new \App\Rules\UniqueUsernameAcrossUsers(
                    ['property_custodians', 'personnel', 'accountings'],
                    'username',
                    'property_custodians',
                    (int) $custodian->id
                ),
            ],
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:property_custodians,email,' . $custodian->id],
            'phone' => ['sometimes', 'nullable', 'string', 'max:255'],
            'position' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            if ($custodian->avatar_path) {
                $oldPath = $custodian->avatar_path;
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $storedName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('avatars', $storedName, 'public');
            $data['avatar_path'] = $path;
        } elseif ($request->boolean('remove_avatar')) {
            if ($custodian->avatar_path && Storage::disk('public')->exists($custodian->avatar_path)) {
                Storage::disk('public')->delete($custodian->avatar_path);
            }
            $data['avatar_path'] = null;
        }

        $custodian->update($data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $custodian->fresh(),
        ]);
    }

    public function changePassword(Request $request)
    {
        /** @var \App\Models\PropertyCustodian $custodian */
        $custodian = $request->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($request->current_password, $custodian->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Your current password is incorrect.'],
            ]);
        }

        if (Hash::check($request->new_password, $custodian->password)) {
            throw ValidationException::withMessages([
                'new_password' => ['New password must be different from the current password.'],
            ]);
        }

        $custodian->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }
}

