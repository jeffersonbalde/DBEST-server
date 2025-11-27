<?php

namespace App\Http\Controllers;

use App\Models\Accounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AccountingController extends Controller
{
    public function index(Request $request)
    {
        $query = Accounting::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
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
            'username' => 'required|string|max:255|unique:accountings,username',
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

    public function show($id)
    {
        $accounting = Accounting::findOrFail($id);
        
        return response()->json($this->transformAccounting($accounting));
    }

    public function update(Request $request, $id)
    {
        $accounting = Accounting::findOrFail($id);

        $validated = $request->validate([
            'username' => 'sometimes|string|max:255|unique:accountings,username,' . $id,
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|unique:accountings,email,' . $id,
            'password' => 'sometimes|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($request->hasFile('avatar')) {
            if ($accounting->avatar_path) {
                Storage::disk('public')->delete($accounting->avatar_path);
            }
            $validated['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $accounting->update($validated);

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
        $accounting->update(['is_active' => true]);

        return response()->json([
            'message' => 'Accounting user activated successfully',
            'user' => $this->transformAccounting($accounting->fresh()),
        ]);
    }

    public function deactivate(Request $request, $id)
    {
        $request->validate([
            'deactivate_reason' => 'nullable|string|max:500',
        ]);

        $accounting = Accounting::findOrFail($id);
        $accounting->update(['is_active' => false]);

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
        ];
    }
}

