<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use Illuminate\Http\Request;

class PersonnelController extends Controller
{
    public function index(Request $request)
    {
        $query = Personnel::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('department')) {
            $query->where('department', $request->department);
        }

        $personnel = $query->orderBy('last_name')->paginate($request->per_page ?? 15);

        return response()->json($personnel);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|unique:personnel',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:personnel',
            'phone' => 'nullable|string',
            'department' => 'nullable|string',
            'position' => 'nullable|string',
            'subject_area' => 'nullable|string',
            'type' => 'nullable|in:teacher,staff,admin',
        ]);

        $personnel = Personnel::create($validated);

        return response()->json($personnel, 201);
    }

    public function show($id)
    {
        $personnel = Personnel::with(['assignedItems.inventoryItem'])->findOrFail($id);
        return response()->json($personnel);
    }

    public function update(Request $request, $id)
    {
        $personnel = Personnel::findOrFail($id);

        $validated = $request->validate([
            'employee_id' => 'sometimes|string|unique:personnel,employee_id,' . $id,
            'first_name' => 'sometimes|string',
            'last_name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:personnel,email,' . $id,
            'phone' => 'nullable|string',
            'department' => 'nullable|string',
            'position' => 'nullable|string',
            'subject_area' => 'nullable|string',
            'type' => 'nullable|in:teacher,staff,admin',
            'is_active' => 'sometimes|boolean',
        ]);

        $personnel->update($validated);

        return response()->json($personnel);
    }

    public function destroy($id)
    {
        $personnel = Personnel::findOrFail($id);
        $personnel->delete();

        return response()->json(['message' => 'Personnel deleted successfully']);
    }
}

