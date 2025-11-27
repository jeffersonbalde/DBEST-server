<?php

namespace App\Http\Controllers;

use App\Models\AssignedItem;
use App\Models\InventoryItem;
use App\Models\Personnel;
use Illuminate\Http\Request;

class AssignedItemController extends Controller
{
    public function index(Request $request)
    {
        $query = AssignedItem::with(['inventoryItem', 'personnel', 'assignedBy']);

        if ($request->has('personnel_id')) {
            $query->where('personnel_id', $request->personnel_id);
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
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'personnel_id' => 'required|exists:personnel,id',
            'quantity' => 'required|integer|min:1',
            'assigned_date' => 'required|date',
            'condition_notes' => 'nullable|string',
        ]);

        $inventoryItem = InventoryItem::findOrFail($validated['inventory_item_id']);

        if ($inventoryItem->available_quantity < $validated['quantity']) {
            return response()->json([
                'message' => 'Insufficient available quantity'
            ], 422);
        }

        $validated['assigned_by'] = $request->user()->id;
        $validated['status'] = 'active';

        $assignedItem = AssignedItem::create($validated);

        // Update inventory item
        $inventoryItem->available_quantity -= $validated['quantity'];
        if ($inventoryItem->available_quantity == 0) {
            $inventoryItem->status = 'assigned';
        }
        $inventoryItem->save();

        return response()->json($assignedItem->load(['inventoryItem', 'personnel', 'assignedBy']), 201);
    }

    public function show($id)
    {
        $item = AssignedItem::with(['inventoryItem', 'personnel', 'assignedBy'])->findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = AssignedItem::findOrFail($id);

        $validated = $request->validate([
            'return_date' => 'nullable|date',
            'status' => 'sometimes|in:active,returned,lost,damaged',
            'condition_notes' => 'nullable|string',
            'return_notes' => 'nullable|string',
        ]);

        $item->update($validated);

        // If returned, update inventory
        if ($item->status === 'returned' && $item->wasChanged('status')) {
            $inventoryItem = $item->inventoryItem;
            $inventoryItem->available_quantity += $item->quantity;
            if ($inventoryItem->status === 'assigned' && $inventoryItem->available_quantity > 0) {
                $inventoryItem->status = 'available';
            }
            $inventoryItem->save();
        }

        return response()->json($item->load(['inventoryItem', 'personnel', 'assignedBy']));
    }

    public function destroy($id)
    {
        $item = AssignedItem::findOrFail($id);
        
        // Return quantity to inventory
        $inventoryItem = $item->inventoryItem;
        $inventoryItem->available_quantity += $item->quantity;
        if ($inventoryItem->status === 'assigned' && $inventoryItem->available_quantity > 0) {
            $inventoryItem->status = 'available';
        }
        $inventoryItem->save();

        $item->delete();

        return response()->json(['message' => 'Assignment deleted successfully']);
    }
}

