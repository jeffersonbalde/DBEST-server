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
            'status' => 'nullable|in:available,assigned,maintenance,disposed',
            'tracking_mode' => 'required|in:detailed,basic',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'supplier' => 'nullable|string',
            'notes' => 'nullable|string',
            'asset_image' => 'nullable|image|max:2048',
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
            'status' => 'nullable|in:available,assigned,maintenance,disposed',
            'tracking_mode' => 'nullable|in:detailed,basic',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'supplier' => 'nullable|string',
            'notes' => 'nullable|string',
            'asset_image' => 'nullable|image|max:2048',
            'remove_image' => 'nullable|boolean',
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
}
