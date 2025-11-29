<?php

namespace App\Http\Controllers;

use App\Models\DcpPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DcpPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $packages = DcpPackage::with('school')
            ->when($request->filled('school_id'), function ($query) use ($request) {
                $query->where('school_id', $request->integer('school_id'));
            })
            ->orderByDesc('delivery_date')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'packages' => $packages,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request, false);
        $data = array_merge($data, $this->handleUploadedDocuments($request));
        $data['created_by'] = $request->user()?->id;

        $package = DcpPackage::create($data);
        $package->load('school');

        return response()->json([
            'message' => 'DCP package created successfully.',
            'package' => $package,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DcpPackage $dcpPackage)
    {
        $dcpPackage->load('school');

        return response()->json([
            'package' => $dcpPackage,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DcpPackage $dcpPackage)
    {
        $data = $this->validateData($request, true);
        $data = array_merge($data, $this->handleUploadedDocuments($request, $dcpPackage));
        $dcpPackage->update($data);

        return response()->json([
            'message' => 'DCP package updated successfully.',
            'package' => $dcpPackage->fresh('school'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DcpPackage $dcpPackage)
    {
        $dcpPackage->delete();

        return response()->json([
            'message' => 'DCP package deleted successfully.',
        ]);
    }

    protected function validateData(Request $request, bool $isUpdate = false): array
    {
        $rule = $isUpdate ? 'sometimes' : 'required';

        return $request->validate([
            'school_id' => [$rule, 'exists:schools,id'],
            'batch_name' => [$rule, 'string', 'max:255'],
            'quantity' => [$isUpdate ? 'sometimes' : 'nullable', 'integer', 'min:1'],
            'package_count' => [$isUpdate ? 'sometimes' : 'nullable', 'integer', 'min:1'],
            'delivery_date' => ['sometimes', 'nullable', 'date'],
            'delivery_status' => ['sometimes', 'nullable', 'string', 'max:255'],
            'installation_status' => ['sometimes', 'nullable', 'string', 'max:255'],
            'details' => ['sometimes', 'nullable', 'string'],
            'remarks' => ['sometimes', 'nullable', 'string'],
            'dr_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'dr_filename' => ['sometimes', 'nullable', 'string', 'max:255'],
            'ptr_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'ptr_filename' => ['sometimes', 'nullable', 'string', 'max:255'],
            'iar_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'iar_filename' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);
    }

    /**
     * Handle DR/PTR/IAR file uploads and return filename updates.
     */
    protected function handleUploadedDocuments(Request $request, ?DcpPackage $existing = null): array
    {
        $updates = [];
        $disk = 'public';

        foreach (['dr', 'ptr', 'iar'] as $prefix) {
            $fileKey = "{$prefix}_file";
            $nameKey = "{$prefix}_filename";

            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);

                // Delete previous file if exists
                if ($existing && $existing->{$nameKey}) {
                    $oldPath = "dcp-documents/{$prefix}/" . $existing->{$nameKey};
                    if (Storage::disk($disk)->exists($oldPath)) {
                        Storage::disk($disk)->delete($oldPath);
                    }
                }

                $storedName = time() . '_' . $file->getClientOriginalName();
                $path = "dcp-documents/{$prefix}";
                $file->storeAs($path, $storedName, $disk);

                $updates[$nameKey] = $storedName;
            }
        }

        return $updates;
    }
}

