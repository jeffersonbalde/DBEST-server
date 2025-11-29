<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryCategoryController extends Controller
{
    /**
     * Display a listing of the resource (paginated).
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $search = $request->input('search');

        $query = InventoryCategory::query()
            ->withCount(['items as items_count']);

        if ($search) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $categories = $query
            ->orderBy('name')
            ->paginate($perPage);

        $stats = [
            'total_categories' => InventoryCategory::count(),
            'total_items' => InventoryItem::count(),
        ];

        return response()->json([
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }

    /**
     * Return a simplified list for dropdowns / comboboxes.
     */
    public function dropdown()
    {
        $categories = InventoryCategory::orderBy('name')->get([
            'id',
            'name',
            'description',
        ]);

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:inventory_categories,name',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:20',
        ]);

        $category = InventoryCategory::create($validated);

        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryCategory $inventoryCategory)
    {
        $inventoryCategory->loadCount('items');
        return response()->json($inventoryCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryCategory $inventoryCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:inventory_categories,name,' . $inventoryCategory->id,
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:20',
        ]);

        $inventoryCategory->update($validated);

        return response()->json($inventoryCategory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryCategory $inventoryCategory)
    {
        $hasItems = InventoryItem::where('category_id', $inventoryCategory->id)
            ->orWhere('category', $inventoryCategory->name)
            ->exists();

        if ($hasItems) {
            return response()->json([
                'message' => 'Cannot delete category with tagged inventory items.',
            ], 422);
        }

        $inventoryCategory->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }
}

