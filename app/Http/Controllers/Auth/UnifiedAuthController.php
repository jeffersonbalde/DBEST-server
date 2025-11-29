<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PropertyCustodian;
use App\Models\Personnel;
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
                    return response()->json([
                        'message' => 'Your account has been deactivated.',
                        'deactivation' => [
                            'reason' => $user->deactivation_reason ?? 'No reason was provided.',
                            'deactivated_at' => $user->deactivated_at,
                            'deactivated_by' => $user->deactivated_by,
                        ],
                    ], 423);
                }

                $token = $user->createToken("{$userType}-token")->plainTextToken;

                return response()->json([
                    'user' => $user,
                    'token' => $token,
                    'user_type' => $userType,
                ]);
            }
        }

        // Check Personnel table (Personnel and Teacher are the same term)
        // Personnel users will be treated as Teachers and routed to /faculty
        $personnelQuery = Personnel::query();
        $personnelQuery->where(function ($q) use ($request) {
            $q->where('username', $request->username)
              ->orWhere('employee_id', $request->username);
        });

        $personnel = $personnelQuery->first();

        if ($personnel && Hash::check($request->password, $personnel->password)) {
            if (!$personnel->is_active) {
                return response()->json([
                    'message' => 'Your account has been deactivated.',
                    'deactivation' => [
                        'reason' => $personnel->deactivation_reason ?? 'No reason was provided.',
                        'deactivated_at' => $personnel->deactivated_at,
                        'deactivated_by' => $personnel->deactivated_by,
                    ],
                ], 423);
            }

            // Return 'teacher' as user_type so Personnel users are routed to /faculty
            $token = $personnel->createToken("teacher-token")->plainTextToken;

            return response()->json([
                'user' => $personnel,
                'token' => $token,
                'user_type' => 'teacher', // Personnel users use Teacher routes
            ]);
        }

        // If no user found in any table
        throw ValidationException::withMessages([
            'username' => ['The provided credentials are incorrect.'],
        ]);
    }
}


