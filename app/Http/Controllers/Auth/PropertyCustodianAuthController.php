<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PropertyCustodian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
}

