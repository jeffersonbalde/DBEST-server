<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\AssignedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function getInventoryAnalytics(Request $request)
    {
        $items = InventoryItem::all();

        $analytics = [
            'total_items' => $items->count(),
            'total_quantity' => $items->sum('quantity'),
            'total_value' => $items->sum('total_value'),
            'available_quantity' => $items->sum('available_quantity'),
            'assigned_quantity' => $items->sum('quantity') - $items->sum('available_quantity'),
            'by_status' => $items->groupBy('status')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'quantity' => $group->sum('quantity'),
                    'value' => $group->sum('total_value'),
                ];
            }),
            'by_category' => $items->groupBy('category')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'quantity' => $group->sum('quantity'),
                    'value' => $group->sum('total_value'),
                ];
            }),
            'top_items_by_value' => $items->sortByDesc('total_value')->take(10)->values(),
            'low_stock_items' => $items->where('available_quantity', '<', 10)->values(),
        ];

        return response()->json($analytics);
    }

    public function getDetailedInventoryList(Request $request)
    {
        $query = InventoryItem::with(['assignedItems.personnel']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 50);

        return response()->json($items);
    }

    public function getFinancialAnalytics(Request $request)
    {
        $items = InventoryItem::all();
        $assignedItems = AssignedItem::where('status', 'active')->get();

        $financial = [
            'total_inventory_value' => $items->sum('total_value'),
            'available_inventory_value' => $items->sum(function($item) {
                return $item->available_quantity * $item->unit_price;
            }),
            'assigned_inventory_value' => $assignedItems->sum(function($item) {
                return $item->quantity * $item->inventoryItem->unit_price;
            }),
            'by_category_value' => $items->groupBy('category')->map(function($group) {
                return [
                    'total_value' => $group->sum('total_value'),
                    'item_count' => $group->count(),
                ];
            }),
            'monthly_purchases' => $this->getMonthlyPurchases($request),
        ];

        return response()->json($financial);
    }

    private function getMonthlyPurchases($request)
    {
        $query = InventoryItem::select(
            DB::raw('YEAR(purchase_date) as year'),
            DB::raw('MONTH(purchase_date) as month'),
            DB::raw('SUM(unit_price * quantity) as total_value'),
            DB::raw('COUNT(*) as item_count')
        )
        ->whereNotNull('purchase_date')
        ->groupBy('year', 'month')
        ->orderBy('year', 'desc')
        ->orderBy('month', 'desc');

        if ($request->has('year')) {
            $query->whereYear('purchase_date', $request->year);
        }

        return $query->get();
    }
}

