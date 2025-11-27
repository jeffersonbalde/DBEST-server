<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $query = School::query();

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('deped_code', 'like', "%{$search}%")
                ->orWhere('division', 'like', "%{$search}%");
        }

        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $schools = $query->orderBy('name')->get();

        return response()->json([
            'schools' => $schools,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:schools,name',
            'deped_code' => 'nullable|string|max:255|unique:schools,deped_code',
            'region' => 'nullable|string|max:255',
            'division' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'website' => 'nullable|string|max:255',
            'avatar_url' => 'nullable|url|max:500',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $school = new School($validated);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('school-avatars', 'public');
            $school->avatar_path = $path;
            $school->avatar_url = null;
        }

        $school->save();

        return response()->json([
            'message' => 'School registered successfully',
            'school' => $school->fresh(),
        ], 201);
    }

    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:schools,name,' . $school->id,
            'deped_code' => 'nullable|string|max:255|unique:schools,deped_code,' . $school->id,
            'region' => 'nullable|string|max:255',
            'division' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'website' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'avatar_url' => 'nullable|url|max:500',
            'avatar' => 'nullable|image|max:2048',
            'remove_avatar' => 'nullable|boolean',
        ]);

        $school->fill($validated);

        if ($request->hasFile('avatar')) {
            if ($school->avatar_path) {
                Storage::disk('public')->delete($school->avatar_path);
            }

            $path = $request->file('avatar')->store('school-avatars', 'public');
            $school->avatar_path = $path;
            $school->avatar_url = null;
        }
        if (!$request->hasFile('avatar') && $request->boolean('remove_avatar')) {
            if ($school->avatar_path) {
                Storage::disk('public')->delete($school->avatar_path);
            }
            $school->avatar_path = null;
            $school->avatar_url = null;
        }

        $school->save();

        return response()->json([
            'message' => 'School updated successfully',
            'school' => $school->fresh(),
        ]);
    }

    public function destroy(School $school)
    {
        if ($school->avatar_path) {
            Storage::disk('public')->delete($school->avatar_path);
        }

        $school->delete();

        return response()->json([
            'message' => 'School removed successfully',
        ]);
    }
}

