<?php

namespace App\Http\Controllers;

use App\Models\Accounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Rules\UniqueUsernameAcrossUsers;

class AccountingController extends Controller
{
    public function index(Request $request)
    {
        $query = Accounting::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $accountings = $query->orderBy('created_at', 'desc')->get()->map(function ($accounting) {
            return $this->transformAccounting($accounting);
        });

        return response()->json(['accountings' => $accountings]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
                new UniqueUsernameAcrossUsers(['accountings', 'property_custodians', 'personnel']),
            ],
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:accountings,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;
        $validated['employee_id'] = Str::upper(Str::random(10));

        if ($request->hasFile('avatar')) {
            $validated['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $accounting = Accounting::create($validated);

        return response()->json([
            'message' => 'Accounting user created successfully',
            'user' => $this->transformAccounting($accounting),
        ], 201);
    }

    public function show(Request $request, $id = null)
    {
        // Handle "me" route for authenticated Accounting users
        // When called from profile/me route, $id will be null
        if ($id === null || $id === 'me') {
            $user = $request->user();

            // User should be an Accounting model
            if ($user instanceof Accounting) {
                $accounting = Accounting::findOrFail($user->id);
                return response()->json(['accounting' => $this->transformAccounting($accounting)]);
            }

            return response()->json(['message' => 'Accounting record not found'], 404);
        }

        $accounting = Accounting::findOrFail($id);
        return response()->json($this->transformAccounting($accounting));
    }

    public function update(Request $request, $id = null)
    {
        // Handle "me" route for authenticated Accounting users
        // When called from profile/me route, $id will be null
        if ($id === null || $id === 'me') {
            $user = $request->user();

            // User should be an Accounting model
            if ($user instanceof Accounting) {
                $accounting = Accounting::findOrFail($user->id);
                $accountingId = $accounting->id;
            } else {
                return response()->json(['message' => 'Accounting record not found'], 404);
            }
        } else {
            $accounting = Accounting::findOrFail($id);
            $accountingId = $accounting->id;
        }

        // For "me" route, only allow updating first_name, last_name, phone, and avatar
        // For admin route (ICT), allow all fields
        $validationRules = [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048',
            'remove_avatar' => 'nullable|boolean',
        ];

        // Only allow email and position updates for admin routes (not for "me")
        if ($id !== null && $id !== 'me') {
            $validationRules['email'] = 'nullable|email|unique:accountings,email,' . $accountingId;
            $validationRules['position'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($validationRules);

        // Filter out empty strings and convert them to null for nullable fields
        $updateData = [];
        foreach ($validated as $key => $value) {
            // Skip fields that shouldn't be updated by the user themselves
            if (in_array($key, ['avatar', 'remove_avatar'])) {
                if (isset($validated[$key])) {
                    $updateData[$key] = $validated[$key];
                }
                continue;
            }

            // Convert empty strings to null for nullable fields
            if ($value === '' || $value === null) {
                $updateData[$key] = null;
            } else {
                $updateData[$key] = $value;
            }
        }

        if ($request->boolean('remove_avatar') && $accounting->avatar_path) {
            Storage::disk('public')->delete($accounting->avatar_path);
            $updateData['avatar_path'] = null;
        }

        if ($request->hasFile('avatar')) {
            if ($accounting->avatar_path) {
                Storage::disk('public')->delete($accounting->avatar_path);
            }
            $updateData['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Only update if there's data to update
        if (!empty($updateData)) {
            $accounting->update($updateData);
            $accounting->refresh();
        }

        // Return in consistent format for 'me' route
        if ($id === null || $id === 'me') {
            return response()->json(['accounting' => $this->transformAccounting($accounting)]);
        }

        return response()->json([
            'message' => 'Accounting user updated successfully',
            'user' => $this->transformAccounting($accounting->fresh()),
        ]);
    }

    public function destroy($id)
    {
        $accounting = Accounting::findOrFail($id);
        if ($accounting->avatar_path) {
            Storage::disk('public')->delete($accounting->avatar_path);
        }
        $accounting->delete();

        return response()->json(['message' => 'Accounting user deleted successfully']);
    }

    public function activate($id)
    {
        $accounting = Accounting::findOrFail($id);
        $accounting->update([
            'is_active' => true,
            'deactivation_reason' => null,
            'deactivated_by' => null,
            'deactivated_at' => null,
        ]);

        return response()->json([
            'message' => 'Accounting user activated successfully',
            'user' => $this->transformAccounting($accounting->fresh()),
        ]);
    }

    public function deactivate(Request $request, $id)
    {
        $request->validate([
            'deactivate_reason' => 'required|string|max:500',
        ]);

        $accounting = Accounting::findOrFail($id);
        $accounting->update([
            'is_active' => false,
            'deactivation_reason' => $request->input('deactivate_reason'),
            'deactivated_by' => optional($request->user())->full_name ?? 'System',
            'deactivated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Accounting user deactivated successfully',
            'user' => $this->transformAccounting($accounting->fresh()),
        ]);
    }

    protected function transformAccounting(Accounting $accounting): array
    {
        return [
            'id' => $accounting->id,
            'username' => $accounting->username,
            'employee_id' => $accounting->employee_id,
            'name' => $accounting->full_name,
            'first_name' => $accounting->first_name,
            'last_name' => $accounting->last_name,
            'email' => $accounting->email,
            'phone' => $accounting->phone,
            'status' => $accounting->is_active ? 'active' : 'inactive',
            'is_active' => $accounting->is_active,
            'avatar_path' => $accounting->avatar_path,
            'avatar_url' => $accounting->avatar_path ? asset('storage/' . $accounting->avatar_path) : null,
            'created_at' => $accounting->created_at,
            'updated_at' => $accounting->updated_at,
            'deactivation_reason' => $accounting->deactivation_reason,
            'deactivated_by' => $accounting->deactivated_by,
            'deactivated_at' => $accounting->deactivated_at,
        ];
    }
}
