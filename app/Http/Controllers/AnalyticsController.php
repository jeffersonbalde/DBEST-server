<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\DcpInventoryItem;
use App\Models\AssignedItem;
use App\Models\School;
use App\Models\PropertyCustodian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function getInventoryAnalytics(Request $request)
    {
        try {
            // Get all school inventory items with personnel
            $schoolItems = InventoryItem::with(['personnel', 'categoryRelation', 'assignedItems.personnel'])->get();

            // Get all DCP inventory items with school and personnel
            $dcpItems = DcpInventoryItem::with(['school', 'personnel', 'package'])->get();

            // Get all schools
            $schools = School::all();

            // Calculate totals
            $totalSchoolItems = $schoolItems->count();
            $totalDcpItems = $dcpItems->count();
            $totalItems = $totalSchoolItems + $totalDcpItems;

            $totalSchoolQuantity = $schoolItems->sum('quantity');
            $totalDcpQuantity = $dcpItems->sum('quantity');
            $totalQuantity = $totalSchoolQuantity + $totalDcpQuantity;

            $totalSchoolValue = $schoolItems->sum('total_value');
            $totalDcpValue = $dcpItems->sum(function ($item) {
                return ($item->unit_value ?? 0) * ($item->quantity ?? 1);
            });
            $totalValue = $totalSchoolValue + $totalDcpValue;

            // Group by school
            $bySchool = [];
            foreach ($schools as $school) {
                // Get property custodian IDs for this school
                $pcIds = PropertyCustodian::where('school_id', $school->id)->pluck('id');

                // Get school inventory items linked to this school through assigned_items
                // Items are linked via assigned_items.assigned_by (property custodian id)
                $schoolInventoryItems = InventoryItem::whereHas('assignedItems', function ($q) use ($pcIds) {
                    $q->whereIn('assigned_by', $pcIds);
                })
                    ->orWhereIn('personnel_id', $pcIds) // Items directly assigned to property custodians
                    ->with(['personnel', 'categoryRelation'])
                    ->get()
                    ->unique('id');

                // Get DCP items for this school
                $schoolDcpItems = $dcpItems->where('school_id', $school->id);

                $schoolItemCount = $schoolInventoryItems->count() + $schoolDcpItems->count();
                if ($schoolItemCount === 0) continue;

                $schoolQuantity = $schoolInventoryItems->sum('quantity') + $schoolDcpItems->sum('quantity');
                $schoolValue = $schoolInventoryItems->sum('total_value') +
                    $schoolDcpItems->sum(function ($item) {
                        return ($item->unit_value ?? 0) * ($item->quantity ?? 1);
                    });

                $assignedSchoolItems = $schoolInventoryItems->whereNotNull('personnel_id')->count();
                $assignedDcpItems = $schoolDcpItems->whereNotNull('personnel_id')->count();

                $bySchool[] = [
                    'school_id' => $school->id,
                    'school_name' => $school->name,
                    'school_code' => $school->deped_code,
                    'total_items' => $schoolItemCount,
                    'school_inventory_count' => $schoolInventoryItems->count(),
                    'dcp_inventory_count' => $schoolDcpItems->count(),
                    'total_quantity' => $schoolQuantity,
                    'total_value' => $schoolValue,
                    'assigned_items' => $assignedSchoolItems + $assignedDcpItems,
                    'unassigned_items' => $schoolItemCount - ($assignedSchoolItems + $assignedDcpItems),
                ];
            }

            // Group by category (combining both types)
            $byCategory = [];
            $allCategories = $schoolItems->pluck('category')->merge($dcpItems->pluck('category'))->filter()->unique();
            foreach ($allCategories as $category) {
                $catSchoolItems = $schoolItems->where('category', $category);
                $catDcpItems = $dcpItems->where('category', $category);

                $byCategory[] = [
                    'category' => $category,
                    'count' => $catSchoolItems->count() + $catDcpItems->count(),
                    'quantity' => $catSchoolItems->sum('quantity') + $catDcpItems->sum('quantity'),
                    'value' => $catSchoolItems->sum('total_value') +
                        $catDcpItems->sum(function ($item) {
                            return ($item->unit_value ?? 0) * ($item->quantity ?? 1);
                        }),
                ];
            }

            // Group by status
            $byStatus = [];
            $allStatuses = $schoolItems->pluck('status')->merge($dcpItems->pluck('condition_status'))->filter()->unique();
            foreach ($allStatuses as $status) {
                $statusSchoolItems = $schoolItems->where('status', $status);
                $statusDcpItems = $dcpItems->where('condition_status', $status);

                $byStatus[] = [
                    'status' => $status,
                    'count' => $statusSchoolItems->count() + $statusDcpItems->count(),
                    'quantity' => $statusSchoolItems->sum('quantity') + $statusDcpItems->sum('quantity'),
                ];
            }

            // Assigned vs Unassigned
            $assignedSchoolCount = $schoolItems->whereNotNull('personnel_id')->count();
            $assignedDcpCount = $dcpItems->whereNotNull('personnel_id')->count();
            $totalAssigned = $assignedSchoolCount + $assignedDcpCount;
            $totalUnassigned = $totalItems - $totalAssigned;

            $analytics = [
                'summary' => [
                    'total_items' => $totalItems,
                    'total_school_inventory' => $totalSchoolItems,
                    'total_dcp_inventory' => $totalDcpItems,
                    'total_quantity' => $totalQuantity,
                    'total_value' => $totalValue,
                    'total_assigned' => $totalAssigned,
                    'total_unassigned' => $totalUnassigned,
                ],
                'by_school' => $bySchool,
                'by_category' => $byCategory,
                'by_status' => $byStatus,
                'assigned_vs_unassigned' => [
                    'assigned' => $totalAssigned,
                    'unassigned' => $totalUnassigned,
                ],
                'school_vs_dcp' => [
                    'school_inventory' => $totalSchoolItems,
                    'dcp_inventory' => $totalDcpItems,
                ],
            ];

            return response()->json($analytics);
        } catch (\Exception $e) {
            \Log::error('Analytics Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Failed to load analytics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDetailedInventoryList(Request $request)
    {
        // Get school inventory items
        $schoolQuery = InventoryItem::with(['personnel', 'categoryRelation', 'assignedItems.personnel']);

        if ($request->has('search')) {
            $search = $request->search;
            $schoolQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if ($request->has('category')) {
            $schoolQuery->where('category', $request->category);
        }

        if ($request->has('status')) {
            $schoolQuery->where('status', $request->status);
        }

        if ($request->has('school_id')) {
            $pcIds = PropertyCustodian::where('school_id', $request->school_id)->pluck('id');
            $schoolQuery->whereHas('assignedItems', function ($q) use ($pcIds) {
                $q->whereIn('assigned_by', $pcIds);
            })->orWhereIn('personnel_id', $pcIds);
        }

        $schoolItems = $schoolQuery->get();

        // Get DCP inventory items
        $dcpQuery = DcpInventoryItem::with(['school', 'personnel', 'package']);

        if ($request->has('search')) {
            $search = $request->search;
            $dcpQuery->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('manufacturer', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('property_no', 'like', "%{$search}%");
            });
        }

        if ($request->has('category')) {
            $dcpQuery->where('category', $request->category);
        }

        if ($request->has('status')) {
            $dcpQuery->where('condition_status', $request->status);
        }

        if ($request->has('school_id')) {
            $dcpQuery->where('school_id', $request->school_id);
        }

        $dcpItems = $dcpQuery->get();

        // Combine and format items
        $allItems = [];

        // Add school inventory items
        foreach ($schoolItems as $item) {
            // Get school through assigned items or property custodian
            $school = null;
            $schoolName = 'Unknown School';
            if ($item->assignedItems->count() > 0) {
                $pc = PropertyCustodian::find($item->assignedItems->first()->assigned_by);
                if ($pc && $pc->school) {
                    $school = $pc->school;
                    $schoolName = $school->name;
                }
            } elseif ($item->personnel_id) {
                $pc = PropertyCustodian::find($item->personnel_id);
                if ($pc && $pc->school) {
                    $school = $pc->school;
                    $schoolName = $school->name;
                }
            }

            $allItems[] = [
                'id' => 'school_' . $item->id,
                'type' => 'school',
                'source' => 'School Inventory',
                'school_id' => $school ? $school->id : null,
                'school_name' => $schoolName,
                'item_code' => $item->item_code,
                'name' => $item->name,
                'description' => $item->description,
                'category' => $item->category,
                'brand' => $item->brand,
                'model' => $item->model,
                'serial_number' => $item->serial_number,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'unit_value' => $item->unit_price,
                'total_value' => $item->total_value,
                'status' => $item->status,
                'condition_status' => $item->status,
                'personnel_id' => $item->personnel_id,
                'personnel' => $item->personnel ? [
                    'id' => $item->personnel->id,
                    'first_name' => $item->personnel->first_name,
                    'last_name' => $item->personnel->last_name,
                    'full_name' => $item->personnel->first_name . ' ' . $item->personnel->last_name,
                ] : null,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        }

        // Add DCP inventory items
        foreach ($dcpItems as $item) {
            $allItems[] = [
                'id' => 'dcp_' . $item->id,
                'type' => 'dcp',
                'source' => 'DCP Package Inventory',
                'school_id' => $item->school_id,
                'school_name' => $item->school ? $item->school->name : 'Unknown School',
                'item_code' => $item->property_no,
                'name' => $item->description,
                'description' => $item->description,
                'category' => $item->category,
                'brand' => $item->manufacturer,
                'model' => $item->model,
                'serial_number' => $item->serial_number,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_value,
                'unit_value' => $item->unit_value,
                'total_value' => ($item->unit_value ?? 0) * ($item->quantity ?? 1),
                'status' => $item->condition_status,
                'condition_status' => $item->condition_status,
                'personnel_id' => $item->personnel_id,
                'personnel' => $item->personnel ? [
                    'id' => $item->personnel->id,
                    'first_name' => $item->personnel->first_name,
                    'last_name' => $item->personnel->last_name,
                    'full_name' => $item->personnel->first_name . ' ' . $item->personnel->last_name,
                ] : null,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        }

        // Sort by created_at desc
        usort($allItems, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // Paginate manually
        $perPage = $request->per_page ?? 50;
        $page = $request->page ?? 1;
        $offset = ($page - 1) * $perPage;
        $paginatedItems = array_slice($allItems, $offset, $perPage);

        return response()->json([
            'data' => $paginatedItems,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => count($allItems),
            'last_page' => ceil(count($allItems) / $perPage),
        ]);
    }

    public function getFinancialAnalytics(Request $request)
    {
        $items = InventoryItem::all();
        $assignedItems = AssignedItem::where('status', 'active')->get();

        $financial = [
            'total_inventory_value' => $items->sum('total_value'),
            'available_inventory_value' => $items->sum(function ($item) {
                return $item->available_quantity * $item->unit_price;
            }),
            'assigned_inventory_value' => $assignedItems->sum(function ($item) {
                return $item->quantity * $item->inventoryItem->unit_price;
            }),
            'by_category_value' => $items->groupBy('category')->map(function ($group) {
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
