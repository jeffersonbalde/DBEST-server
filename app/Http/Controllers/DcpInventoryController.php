<?php

namespace App\Http\Controllers;

use App\Models\DcpInventoryItem;
use App\Models\DcpPackage;
use Illuminate\Http\Request;

class DcpInventoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = DcpInventoryItem::query()
            ->with(['package', 'personnel']);

        // Filter by school_id if user has it (Property Custodian)
        // For teachers/personnel, they can filter by personnel_id
        if (isset($user->school_id)) {
            $query->where('school_id', $user->school_id);
        }

        // Allow filtering by personnel_id for teachers
        if ($request->filled('personnel_id')) {
            $query->where('personnel_id', $request->integer('personnel_id'));
        }

        if ($request->filled('dcp_package_id')) {
            $query->where('dcp_package_id', $request->integer('dcp_package_id'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('batch_name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('property_no', 'like', "%{$search}%")
                    ->orWhereHas('personnel', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $items = $query
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 50);

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $this->validatePayload($request);

        $package = DcpPackage::findOrFail($validated['dcp_package_id']);
        if ((int) $package->school_id !== (int) $user->school_id) {
            abort(403, 'You are not allowed to add inventory for this package.');
        }

        $payload = array_merge($validated, [
            'school_id' => $user->school_id,
            'batch_name' => $package->batch_name,
        ]);

        $item = DcpInventoryItem::create($payload);

        return response()->json([
            'message' => 'DCP inventory item created successfully.',
            'item' => $item->fresh(['package', 'personnel']),
        ], 201);
    }

    public function update(Request $request, DcpInventoryItem $dcpInventoryItem)
    {
        $validated = $this->validatePayload($request, true);

        // If package is changed, ensure it still belongs to the same school.
        if (isset($validated['dcp_package_id'])) {
            $package = DcpPackage::findOrFail($validated['dcp_package_id']);
            $validated['batch_name'] = $package->batch_name;
        }

        $dcpInventoryItem->update($validated);

        return response()->json([
            'message' => 'DCP inventory item updated successfully.',
            'item' => $dcpInventoryItem->fresh(['package', 'personnel']),
        ]);
    }

    public function destroy(Request $request, DcpInventoryItem $dcpInventoryItem)
    {
        $dcpInventoryItem->delete();

        return response()->json([
            'message' => 'DCP inventory item deleted successfully.',
        ]);
    }

    protected function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $rule = $isUpdate ? 'sometimes' : 'required';

        $validated = $request->validate([
            'dcp_package_id' => [$rule, 'exists:dcp_packages,id'],
            'category' => [$rule, 'string', 'max:255'],
            'description' => [$rule, 'string'],
            'manufacturer' => [$rule, 'string', 'max:255'],
            'model' => [$rule, 'string', 'max:255'],
            'serial_number' => [$rule, 'string', 'max:255'],
            'unit_of_measure' => ['sometimes', 'nullable', 'string', 'max:50'],
            'unit_value' => [$rule, 'numeric', 'min:0'],
            'quantity' => [$rule, 'integer', 'min:1'],
            'property_no' => [$rule, 'string', 'max:255'],
            'personnel_id' => [$rule, 'exists:personnel,id'],
            'condition_status' => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_checked_at' => ['sometimes', 'nullable', 'date'],
            'validation_status' => ['sometimes', 'nullable', 'string', 'max:255'],
            'remarks' => ['sometimes', 'nullable', 'string'],
        ]);

        // Remove personnel_name and personnel_position - we only store personnel_id
        // The name and position will be retrieved from the personnel relationship
        unset($validated['personnel_name']);
        unset($validated['personnel_position']);

        return $validated;
    }
}


