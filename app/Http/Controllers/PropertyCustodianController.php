<?php

namespace App\Http\Controllers;

use App\Models\PropertyCustodian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PropertyCustodianController extends Controller
{
    public function index(Request $request)
    {
        $query = PropertyCustodian::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $custodians = $query->orderBy('last_name')->paginate($request->per_page ?? 15);

        return response()->json($custodians);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|unique:property_custodians',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:property_custodians',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string',
            'position' => 'nullable|string',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $custodian = PropertyCustodian::create($validated);

        return response()->json($custodian, 201);
    }

    public function show($id)
    {
        $custodian = PropertyCustodian::findOrFail($id);
        return response()->json($custodian);
    }

    public function update(Request $request, $id)
    {
        $custodian = PropertyCustodian::findOrFail($id);

        $validated = $request->validate([
            'employee_id' => 'sometimes|string|unique:property_custodians,employee_id,' . $id,
            'first_name' => 'sometimes|string',
            'last_name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:property_custodians,email,' . $id,
            'password' => 'sometimes|string|min:8',
            'phone' => 'nullable|string',
            'position' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $custodian->update($validated);

        return response()->json($custodian);
    }

    public function destroy($id)
    {
        $custodian = PropertyCustodian::findOrFail($id);
        $custodian->delete();

        return response()->json(['message' => 'Property custodian deleted successfully']);
    }
}

