<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Rules\UniqueUsernameAcrossUsers;

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
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('department')) {
            $query->where('department', $request->department);
        }

        $personnel = $query
            ->with(['assignedItems.inventoryItem'])
            ->orderBy('last_name')
            ->paginate($request->per_page ?? 15);
        $personnel->getCollection()->transform(function ($record) {
            return $this->transformPersonnel($record);
        });

        return response()->json($personnel);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|max:50|unique:personnel,employee_id',
            'id_number' => 'required|string|max:50|unique:personnel,id_number',
            'username' => [
                'required',
                'string',
                'max:255',
                new UniqueUsernameAcrossUsers(['personnel', 'property_custodians', 'accountings']),
            ],
            'first_name' => 'required|string|max:120',
            'last_name' => 'required|string|max:120',
            'email' => 'nullable|email|max:255|unique:personnel,email',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:120',
            'position' => 'nullable|string|max:120',
            'subject_area' => 'nullable|string|max:120',
            'employment_status' => 'required|string|max:120',
            'employment_level' => 'required|string|max:120',
            'rating' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'type' => 'nullable|in:teacher,staff,admin',
            'is_active' => 'nullable|boolean',
            'password' => 'required|string|min:6|confirmed',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $payload = $validated;
        $payload['password'] = Hash::make($validated['password']);
        $payload['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('avatar')) {
            $payload['avatar_path'] = $request->file('avatar')->store('personnel-avatars', 'public');
        }

        $personnel = Personnel::create($payload);

        return response()->json($this->transformPersonnel($personnel->fresh()), 201);
    }

    public function show(Request $request, $id)
    {
        // Handle "me" route for authenticated Personnel users
        if ($id === 'me') {
            $user = $request->user();
            
            // User should be a Personnel model
            if ($user instanceof Personnel) {
                $personnel = Personnel::with(['assignedItems.inventoryItem'])->findOrFail($user->id);
                return response()->json(['personnel' => $this->transformPersonnel($personnel)]);
            }
            
            return response()->json(['message' => 'Personnel record not found'], 404);
        }
        
        $personnel = Personnel::with(['assignedItems.inventoryItem'])->findOrFail($id);
        return response()->json($this->transformPersonnel($personnel));
    }

    public function update(Request $request, $id)
    {
        // Debug: Log raw request data
        \Log::info('Update request received', [
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'all_data' => $request->all(),
            'input_data' => $request->input(),
            'has_file' => $request->hasFile('avatar'),
        ]);
        
        // Handle "me" route for authenticated Personnel users
        if ($id === 'me') {
            $user = $request->user();
            
            // User should be a Personnel model
            if ($user instanceof Personnel) {
                $personnel = Personnel::findOrFail($user->id);
                $personnelId = $personnel->id;
            } else {
                return response()->json(['message' => 'Personnel record not found'], 404);
            }
        } else {
            $personnel = Personnel::findOrFail($id);
            $personnelId = $personnel->id;
        }

        $validated = $request->validate([
            'employee_id' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('personnel', 'employee_id')->ignore($personnelId),
            ],
            'id_number' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('personnel', 'id_number')->ignore($personnelId),
            ],
            'username' => [
                'sometimes',
                'string',
                'max:255',
                new UniqueUsernameAcrossUsers(
                    ['personnel', 'property_custodians', 'accountings'],
                    'username',
                    'personnel',
                    $personnelId
                ),
            ],
            'first_name' => 'sometimes|string|max:120',
            'last_name' => 'sometimes|string|max:120',
            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
                Rule::unique('personnel', 'email')->ignore($personnelId),
            ],
            'phone' => 'sometimes|nullable|string|max:20',
            'department' => 'sometimes|nullable|string|max:120',
            'position' => 'sometimes|nullable|string|max:120',
            'subject_area' => 'sometimes|nullable|string|max:120',
            'employment_status' => 'sometimes|nullable|string|max:120',
            'employment_level' => 'sometimes|nullable|string|max:120',
            'rating' => 'sometimes|nullable|string|max:50',
            'notes' => 'sometimes|nullable|string',
            'type' => 'sometimes|nullable|in:teacher,staff,admin',
            'is_active' => 'sometimes|boolean',
            'password' => 'nullable|string|min:6|confirmed',
            'avatar' => 'nullable|image|max:2048',
            'remove_avatar' => 'nullable|boolean',
        ]);
        
        // Filter out empty strings and convert them to null for nullable fields
        $updateData = [];
        foreach ($validated as $key => $value) {
            // Skip fields that shouldn't be updated
            if (in_array($key, ['employee_id', 'id_number', 'username', 'password', 'avatar', 'remove_avatar'])) {
                if ($key === 'remove_avatar' || $key === 'avatar') {
                    // Keep these special fields
                    if (isset($validated[$key])) {
                        $updateData[$key] = $validated[$key];
                    }
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

        if ($request->boolean('remove_avatar') && $personnel->avatar_path) {
            Storage::disk('public')->delete($personnel->avatar_path);
            $updateData['avatar_path'] = null;
        }

        if ($request->hasFile('avatar')) {
            if ($personnel->avatar_path) {
                Storage::disk('public')->delete($personnel->avatar_path);
            }
            $updateData['avatar_path'] = $request->file('avatar')->store('personnel-avatars', 'public');
        }

        if (isset($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        // Debug: Log what we're updating
        \Log::info('Updating personnel', [
            'personnel_id' => $personnel->id,
            'update_data' => $updateData,
            'validated' => $validated,
            'request_all' => $request->all(),
        ]);
        
        // Only update if there's data to update
        if (!empty($updateData)) {
            $result = $personnel->update($updateData);
            \Log::info('Personnel update result', [
                'personnel_id' => $personnel->id,
                'result' => $result,
                'updated_data' => $updateData,
            ]);
            
            // Force refresh from database
            $personnel->refresh();
            \Log::info('Personnel after refresh', [
                'personnel_id' => $personnel->id,
                'first_name' => $personnel->first_name,
                'last_name' => $personnel->last_name,
                'phone' => $personnel->phone,
            ]);
        } else {
            \Log::warning('No data to update', [
                'personnel_id' => $personnel->id,
                'validated' => $validated,
            ]);
        }

        // Reload the model with relationships to ensure we have the latest data
        $personnel->load(['assignedItems.inventoryItem']);

        // Return in consistent format for 'me' route
        if ($id === 'me') {
            return response()->json(['personnel' => $this->transformPersonnel($personnel)]);
        }
        
        return response()->json($this->transformPersonnel($personnel));
    }

    public function destroy($id)
    {
        $personnel = Personnel::findOrFail($id);
        if ($personnel->avatar_path) {
            Storage::disk('public')->delete($personnel->avatar_path);
        }
        $personnel->delete();

        return response()->json(['message' => 'Personnel deleted successfully']);
    }

    public function activate($id)
    {
        $personnel = Personnel::findOrFail($id);
        $personnel->update([
            'is_active' => true,
            'deactivation_reason' => null,
            'deactivated_by' => null,
            'deactivated_at' => null,
        ]);

        return response()->json([
            'message' => 'Personnel activated successfully',
            'personnel' => $this->transformPersonnel($personnel->fresh()),
        ]);
    }

    public function deactivate(Request $request, $id)
    {
        $request->validate([
            'deactivate_reason' => 'required|string|max:500',
        ]);

        $personnel = Personnel::findOrFail($id);
        $actor = $request->user();

        $personnel->update([
            'is_active' => false,
            'deactivation_reason' => $request->input('deactivate_reason'),
            'deactivated_by' => $actor ? ($actor->full_name ?? $actor->first_name ?? 'System') : 'System',
            'deactivated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Personnel deactivated successfully',
            'personnel' => $this->transformPersonnel($personnel->fresh()),
        ]);
    }

    protected function transformPersonnel(Personnel $personnel): array
    {
        return [
            'id' => $personnel->id,
            'employee_id' => $personnel->employee_id,
            'id_number' => $personnel->id_number,
            'username' => $personnel->username,
            'first_name' => $personnel->first_name,
            'last_name' => $personnel->last_name,
            'full_name' => $personnel->full_name,
            'email' => $personnel->email,
            'phone' => $personnel->phone,
            'department' => $personnel->department,
            'position' => $personnel->position,
            'subject_area' => $personnel->subject_area,
            'employment_status' => $personnel->employment_status,
            'employment_level' => $personnel->employment_level,
            'rating' => $personnel->rating,
            'type' => $personnel->type,
            'notes' => $personnel->notes,
            'is_active' => $personnel->is_active,
            'avatar_path' => $personnel->avatar_path,
            'avatar_url' => $personnel->avatar_url,
            'deactivation_reason' => $personnel->deactivation_reason,
            'deactivated_by' => $personnel->deactivated_by,
            'deactivated_at' => $personnel->deactivated_at,
            'created_at' => $personnel->created_at,
            'updated_at' => $personnel->updated_at,
            'assigned_items' => $personnel->relationLoaded('assignedItems')
                ? $personnel->assignedItems
                : null,
        ];
    }
}

