<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::query()->with('categoryRelation');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        } elseif ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query
            ->orderBy($request->get('sort_field', 'created_at'), $request->get('sort_direction', 'desc'))
            ->paginate($request->per_page ?? 15);

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_code' => 'nullable|string|unique:inventory_items,item_code',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:inventory_categories,id',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'unit_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'available_quantity' => 'required|integer|min:0',
            'unit_of_measure' => 'nullable|string',
            'location' => 'nullable|string',
            'status' => 'nullable|in:SERVICEABLE,UNSERVICEABLE,NEEDS REPAIR,MISSING/LOST',
            'tracking_mode' => 'required|in:detailed,basic',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'supplier' => 'nullable|string',
            'notes' => 'nullable|string',
            'asset_image' => 'nullable|image|max:2048',
            'personnel_id' => 'nullable|exists:personnel,id',
        ]);

        // Ensure non-null fields that have DB defaults are not saved as NULL
        // so we don't hit integrity constraint violations when some fields are omitted.
        if (!array_key_exists('unit_of_measure', $validated) || $validated['unit_of_measure'] === null) {
            $validated['unit_of_measure'] = 'pcs';
        }

        if (empty($validated['item_code'])) {
            $validated['item_code'] = InventoryItem::generateItemCode($validated['tracking_mode']);
        }

        if ($request->hasFile('asset_image')) {
            $validated['image_path'] = $request->file('asset_image')->store('inventory-assets', 'public');
        }

        $category = InventoryCategory::find($validated['category_id']);
        $validated['category'] = $category?->name;

        $item = InventoryItem::create($validated);

        return response()->json($item->fresh('categoryRelation'), 201);
    }

    public function show($id)
    {
        $item = InventoryItem::with([
            'assignedItems.personnel',
            'assignedItems.assignedBy',
            'categoryRelation',
        ])->findOrFail($id);

        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = InventoryItem::findOrFail($id);

        $validated = $request->validate([
            'item_code' => 'nullable|string|unique:inventory_items,item_code,' . $id,
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:inventory_categories,id',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'unit_price' => 'nullable|numeric|min:0',
            'quantity' => 'sometimes|integer|min:0',
            'available_quantity' => 'sometimes|integer|min:0',
            'unit_of_measure' => 'nullable|string',
            'location' => 'nullable|string',
            'status' => 'nullable|in:SERVICEABLE,UNSERVICEABLE,NEEDS REPAIR,MISSING/LOST',
            'tracking_mode' => 'nullable|in:detailed,basic',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'supplier' => 'nullable|string',
            'notes' => 'nullable|string',
            'asset_image' => 'nullable|image|max:2048',
            'remove_image' => 'nullable|boolean',
            'personnel_id' => 'nullable|exists:personnel,id',
        ]);

        if ($request->boolean('remove_image') && $item->image_path) {
            Storage::disk('public')->delete($item->image_path);
            $validated['image_path'] = null;
        }

        if ($request->hasFile('asset_image')) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $validated['image_path'] = $request
                ->file('asset_image')
                ->store('inventory-assets', 'public');
        }

        if (isset($validated['category_id'])) {
            $category = InventoryCategory::find($validated['category_id']);
            $validated['category'] = $category?->name;
        }

        if (isset($validated['tracking_mode']) && empty($validated['item_code'])) {
            $validated['item_code'] = InventoryItem::generateItemCode($validated['tracking_mode']);
        }

        $item->update($validated);

        return response()->json($item->fresh('categoryRelation'));
    }

    public function destroy($id)
    {
        $item = InventoryItem::findOrFail($id);

        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted successfully']);
    }

    public function generateICS(Request $request, $id)
    {
        $item = InventoryItem::with(['categoryRelation'])->findOrFail($id);
        $user = $request->user();
        
        $validated = $request->validate([
            'fund_cluster' => 'required|string|max:255',
            'ics_number' => 'nullable|string|max:255',
            'estimated_useful_life' => 'required|string|max:255',
            'received_by_name' => 'required|string|max:255',
            'received_by_position' => 'nullable|string|max:255',
            'received_from_name' => 'required|string|max:255',
            'received_from_position' => 'required|string|max:255',
            'date' => 'required|date',
        ]);

        // Generate ICS number if not provided
        if (empty($validated['ics_number'])) {
            $now = now();
            $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $validated['ics_number'] = sprintf(
                'SPL-ICS-LV-%s-%s-%s',
                $now->format('Y'),
                $now->format('m'),
                $random
            );
        }

        // Get personnel if assigned
        $personnel = null;
        if ($item->personnel_id) {
            $personnel = \App\Models\Personnel::find($item->personnel_id);
        }

        // Get school info
        $school = null;
        if ($user->school_id) {
            $school = \App\Models\School::find($user->school_id);
        }

        // Use DomPDF or similar for PDF generation
        // For now, we'll return JSON and let frontend handle PDF generation
        // Or we can use a package like barryvdh/laravel-dompdf
        
        return response()->json([
            'ics_data' => [
                'ics_number' => $validated['ics_number'],
                'fund_cluster' => $validated['fund_cluster'],
                'estimated_useful_life' => $validated['estimated_useful_life'],
                'item' => [
                    'item_code' => $item->item_code,
                    'name' => $item->name,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit_of_measure,
                    'unit_cost' => $item->unit_price,
                    'total_cost' => $item->quantity * $item->unit_price,
                ],
                'personnel' => $personnel ? [
                    'name' => $validated['received_by_name'],
                    'position' => $validated['received_by_position'],
                ] : null,
                'school' => $school ? [
                    'name' => $school->name,
                ] : null,
                'received_from' => [
                    'name' => $validated['received_from_name'],
                    'position' => $validated['received_from_position'],
                ],
                'date' => $validated['date'],
            ],
        ]);
    }
}
