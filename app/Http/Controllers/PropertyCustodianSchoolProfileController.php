<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PropertyCustodianSchoolProfileController extends Controller
{
    /**
     * Return the school profile for the authenticated property custodian.
     */
    public function show(Request $request)
    {
      /** @var \App\Models\PropertyCustodian $custodian */
        $custodian = $request->user();

        if (!$custodian || !$custodian->school) {
            return response()->json([
                'message' => 'No school profile is linked to this account. Please contact your ICT administrator.',
            ], 404);
        }

        return response()->json([
            'school' => $custodian->school,
        ]);
    }

    /**
     * Update the school profile for the authenticated property custodian.
     * This reuses the same validation rules as SchoolController@update but is scoped
     * only to the custodian's linked school.
     */
    public function update(Request $request)
    {
        /** @var \App\Models\PropertyCustodian $custodian */
        $custodian = $request->user();

        if (!$custodian || !$custodian->school) {
            return response()->json([
                'message' => 'No school profile is linked to this account. Please contact your ICT administrator.',
            ], 404);
        }

        /** @var \App\Models\School $school */
        $school = $custodian->school;

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
            'message' => 'School profile updated successfully',
            'school' => $school->fresh(),
        ]);
    }
}


