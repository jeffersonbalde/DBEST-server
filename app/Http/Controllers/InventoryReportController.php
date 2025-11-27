<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryReport;
use App\Models\AssignedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = InventoryReport::orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($reports);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|string',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'filters' => 'nullable|array',
            'format' => 'nullable|in:json,pdf,excel',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $data = $this->generateReportData($validated['report_type'], $validated['filters'] ?? []);

        $validated['generated_by'] = $request->user()->id;
        $validated['data'] = $data;
        $validated['format'] = $validated['format'] ?? 'json';

        $report = InventoryReport::create($validated);

        return response()->json($report->load('generator'), 201);
    }

    public function show($id)
    {
        $report = InventoryReport::with('generator')->findOrFail($id);
        return response()->json($report);
    }

    private function generateReportData($reportType, $filters = [])
    {
        return match($reportType) {
            'inventory_summary' => $this->getInventorySummary($filters),
            'assigned_items' => $this->getAssignedItemsReport($filters),
            'category_breakdown' => $this->getCategoryBreakdown($filters),
            'personnel_assignments' => $this->getPersonnelAssignments($filters),
            default => [],
        };
    }

    private function getInventorySummary($filters)
    {
        $query = InventoryItem::query();

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $items = $query->get();

        return [
            'total_items' => $items->count(),
            'total_quantity' => $items->sum('quantity'),
            'total_value' => $items->sum('total_value'),
            'available_quantity' => $items->sum('available_quantity'),
            'by_status' => $items->groupBy('status')->map->count(),
            'by_category' => $items->groupBy('category')->map->count(),
        ];
    }

    private function getAssignedItemsReport($filters)
    {
        $query = AssignedItem::with(['inventoryItem', 'personnel']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->where('assigned_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('assigned_date', '<=', $filters['date_to']);
        }

        return $query->get();
    }

    private function getCategoryBreakdown($filters)
    {
        return InventoryItem::select('category', DB::raw('count(*) as count'), DB::raw('sum(quantity) as total_quantity'))
            ->groupBy('category')
            ->get();
    }

    private function getPersonnelAssignments($filters)
    {
        $query = AssignedItem::with(['inventoryItem', 'personnel']);

        if (isset($filters['personnel_id'])) {
            $query->where('personnel_id', $filters['personnel_id']);
        }

        return $query->get()->groupBy('personnel_id');
    }
}

