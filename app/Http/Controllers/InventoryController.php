<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_code' => 'required|string|unique:inventory_items',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'unit_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'available_quantity' => 'required|integer|min:0',
            'unit_of_measure' => 'nullable|string',
            'location' => 'nullable|string',
            'status' => 'nullable|in:available,assigned,maintenance,disposed',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'supplier' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $item = InventoryItem::create($validated);

        return response()->json($item, 201);
    }

    public function show($id)
    {
        $item = InventoryItem::with(['assignedItems.personnel', 'assignedItems.assignedBy'])->findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = InventoryItem::findOrFail($id);

        $validated = $request->validate([
            'item_code' => 'sometimes|string|unique:inventory_items,item_code,' . $id,
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'unit_price' => 'nullable|numeric|min:0',
            'quantity' => 'sometimes|integer|min:0',
            'available_quantity' => 'sometimes|integer|min:0',
            'unit_of_measure' => 'nullable|string',
            'location' => 'nullable|string',
            'status' => 'nullable|in:available,assigned,maintenance,disposed',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'supplier' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $item->update($validated);

        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = InventoryItem::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Item deleted successfully']);
    }

    public function getCategories()
    {
        $categories = InventoryItem::distinct()->pluck('category')->filter()->values();
        return response()->json($categories);
    }
}

