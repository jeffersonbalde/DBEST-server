<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Accounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AccountingAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $accounting = Accounting::where('email', $request->email)->first();

        if (!$accounting || !Hash::check($request->password, $accounting->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$accounting->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        $token = $accounting->createToken('accounting-token')->plainTextToken;

        return response()->json([
            'user' => $accounting,
            'token' => $token,
            'user_type' => 'accounting',
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|unique:accountings',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:accountings',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string',
            'position' => 'nullable|string',
        ]);

        $accounting = Accounting::create([
            'employee_id' => $request->employee_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'position' => $request->position,
        ]);

        $token = $accounting->createToken('accounting-token')->plainTextToken;

        return response()->json([
            'user' => $accounting,
            'token' => $token,
            'user_type' => 'accounting',
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

