<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PropertyCustodian;
use App\Models\Teacher;
use App\Models\Ict;
use App\Models\Accounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UnifiedAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        $userTypes = [
            'property_custodian' => PropertyCustodian::class,
            'teacher' => Teacher::class,
            'ict' => Ict::class,
            'accounting' => Accounting::class,
        ];

        // Try to find user in each table
        foreach ($userTypes as $userType => $modelClass) {
            $model = new $modelClass;
            $query = $modelClass::query();

            if (in_array('username', $model->getFillable())) {
                $query->where(function ($q) use ($request) {
                    $q->where('username', $request->username)
                      ->orWhere('employee_id', $request->username);
                });
            } else {
                $query->where('employee_id', $request->username);
            }

            $user = $query->first();

            if ($user && Hash::check($request->password, $user->password)) {
                if (!$user->is_active) {
                    throw ValidationException::withMessages([
                        'username' => ['Your account has been deactivated.'],
                    ]);
                }

                $token = $user->createToken("{$userType}-token")->plainTextToken;

                return response()->json([
                    'user' => $user,
                    'token' => $token,
                    'user_type' => $userType,
                ]);
            }
        }

        // If no user found in any table
        throw ValidationException::withMessages([
            'username' => ['The provided credentials are incorrect.'],
        ]);
    }
}


