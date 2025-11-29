<?php

namespace App\Http\Controllers;

use App\Models\DcpPackage;
use Illuminate\Http\Request;

class PropertyCustodianDcpPackageController extends Controller
{
    public function index(Request $request)
    {
        $custodian = $request->user();

        $packages = DcpPackage::with('school')
            ->where('school_id', $custodian->school_id)
            ->orderByDesc('delivery_date')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'packages' => $packages,
        ]);
    }

    public function update(Request $request, DcpPackage $dcpPackage)
    {
        $custodian = $request->user();

        if ((int) $dcpPackage->school_id !== (int) $custodian->school_id) {
            abort(403, 'You are not allowed to update this package.');
        }

        $data = $request->validate([
            'delivery_date' => ['sometimes', 'nullable', 'date'],
            'delivery_status' => ['sometimes', 'nullable', 'string', 'max:255'],
            'installation_status' => ['sometimes', 'nullable', 'string', 'max:255'],
            'remarks' => ['sometimes', 'nullable', 'string'],
            'dr_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'dr_filename' => ['sometimes', 'nullable', 'string', 'max:255'],
            'ptr_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'ptr_filename' => ['sometimes', 'nullable', 'string', 'max:255'],
            'iar_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'iar_filename' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $dcpPackage->update($data);

        return response()->json([
            'message' => 'Package updated successfully.',
            'package' => $dcpPackage->fresh('school'),
        ]);
    }
}

