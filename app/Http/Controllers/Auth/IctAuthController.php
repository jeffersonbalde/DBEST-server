<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Ict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class IctAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $ict = Ict::where('email', $request->email)->first();

        if (!$ict || !Hash::check($request->password, $ict->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$ict->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        $token = $ict->createToken('ict-token')->plainTextToken;

        return response()->json([
            'user' => $ict,
            'token' => $token,
            'user_type' => 'ict',
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|unique:icts',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:icts',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string',
            'position' => 'nullable|string',
        ]);

        $ict = Ict::create([
            'employee_id' => $request->employee_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'position' => $request->position,
        ]);

        $token = $ict->createToken('ict-token')->plainTextToken;

        return response()->json([
            'user' => $ict,
            'token' => $token,
            'user_type' => 'ict',
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
}

