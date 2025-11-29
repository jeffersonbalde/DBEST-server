<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TeacherAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $teacher = Teacher::where('email', $request->email)->first();

        if (!$teacher || !Hash::check($request->password, $teacher->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$teacher->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        $token = $teacher->createToken('teacher-token')->plainTextToken;

        return response()->json([
            'user' => $teacher,
            'token' => $token,
            'user_type' => 'teacher',
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|unique:teachers',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:teachers',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string',
            'department' => 'nullable|string',
            'subject_area' => 'nullable|string',
        ]);

        $teacher = Teacher::create([
            'employee_id' => $request->employee_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'department' => $request->department,
            'subject_area' => $request->subject_area,
        ]);

        $token = $teacher->createToken('teacher-token')->plainTextToken;

        return response()->json([
            'user' => $teacher,
            'token' => $token,
            'user_type' => 'teacher',
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

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        /** @var \App\Models\Personnel $user */
        $user = $request->user();

        // User should be a Personnel model (teachers = personnel)
        if (!$user || !$user instanceof Personnel) {
            throw ValidationException::withMessages([
                'current_password' => ['Unable to verify personnel account.'],
            ]);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Your current password is incorrect.'],
            ]);
        }

        if (Hash::check($request->new_password, $user->password)) {
            throw ValidationException::withMessages([
                'new_password' => ['New password must be different from the current password.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }
}

