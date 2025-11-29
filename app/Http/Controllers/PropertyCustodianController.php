<?php

namespace App\Http\Controllers;

use App\Models\PropertyCustodian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PropertyCustodianController extends Controller
{
    public function index(Request $request)
    {
        $query = PropertyCustodian::query()->with('school');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $custodians = $query
            ->orderBy('last_name')
            ->paginate($request->per_page ?? 15);

        $custodians->getCollection()->transform(function ($custodian) {
            return $this->transformCustodian($custodian);
        });

        return response()->json($custodians);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:property_custodians,username',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'school_id' => 'nullable|exists:schools,id|unique:property_custodians,school_id',
            'email' => 'nullable|email|max:255|unique:property_custodians,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['employee_id'] = Str::upper(Str::random(10));
        $validated['is_active'] = true; // Set default account status to Active

        if ($request->hasFile('avatar')) {
            $validated['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $custodian = PropertyCustodian::create($validated)->load('school');

        return response()->json([
            'message' => 'Property custodian created successfully',
            'custodian' => $this->transformCustodian($custodian),
        ], 201);
    }

    public function show($id)
    {
        $custodian = PropertyCustodian::with('school')->findOrFail($id);
        return response()->json($this->transformCustodian($custodian));
    }

    public function update(Request $request, $id)
    {
        $custodian = PropertyCustodian::findOrFail($id);

        $validated = $request->validate([
            'username' => 'sometimes|string|max:255|unique:property_custodians,username,' . $id,
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'school_id' => 'nullable|exists:schools,id|unique:property_custodians,school_id,' . $id,
            'email' => 'nullable|email|max:255|unique:property_custodians,email,' . $id,
            'password' => 'sometimes|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($request->hasFile('avatar')) {
            if ($custodian->avatar_path) {
                Storage::disk('public')->delete($custodian->avatar_path);
            }
            $validated['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $custodian->update($validated);

        return response()->json([
            'message' => 'Property custodian updated successfully',
            'custodian' => $this->transformCustodian($custodian->fresh('school')),
        ]);
    }

    public function destroy($id)
    {
        $custodian = PropertyCustodian::findOrFail($id);
        if ($custodian->avatar_path) {
            Storage::disk('public')->delete($custodian->avatar_path);
        }
        $custodian->delete();

        return response()->json(['message' => 'Property custodian deleted successfully']);
    }

    public function activate($id)
    {
        $custodian = PropertyCustodian::findOrFail($id);
        $custodian->update([
            'is_active' => true,
            'deactivation_reason' => null,
            'deactivated_by' => null,
            'deactivated_at' => null,
        ]);

        return response()->json([
            'message' => 'Property custodian activated successfully',
            'custodian' => $this->transformCustodian($custodian->fresh('school'))
        ]);
    }

    public function deactivate(Request $request, $id)
    {
        $request->validate([
            'deactivate_reason' => 'required|string|max:500',
        ]);

        $custodian = PropertyCustodian::findOrFail($id);
        $custodian->update([
            'is_active' => false,
            'deactivation_reason' => $request->input('deactivate_reason'),
            'deactivated_by' => optional($request->user())->full_name ?? 'System',
            'deactivated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Property custodian deactivated successfully',
            'custodian' => $this->transformCustodian($custodian->fresh('school'))
        ]);
    }

    protected function transformCustodian(PropertyCustodian $custodian): array
    {
        return [
            'id' => $custodian->id,
            'username' => $custodian->username,
            'employee_id' => $custodian->employee_id,
            'first_name' => $custodian->first_name,
            'last_name' => $custodian->last_name,
            'full_name' => $custodian->full_name,
            'email' => $custodian->email,
            'phone' => $custodian->phone,
            'is_active' => $custodian->is_active,
            'school_id' => $custodian->school_id,
            'school' => $custodian->school,
            'avatar_path' => $custodian->avatar_path,
            'avatar_url' => $custodian->avatar_path ? asset('storage/' . $custodian->avatar_path) : null,
            'created_at' => $custodian->created_at,
            'updated_at' => $custodian->updated_at,
            'deactivation_reason' => $custodian->deactivation_reason,
            'deactivated_by' => $custodian->deactivated_by,
            'deactivated_at' => $custodian->deactivated_at,
        ];
    }
}
