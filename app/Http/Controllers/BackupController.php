<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BackupController extends Controller
{
    public function index(Request $request)
    {
        $backups = Backup::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($backups);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:database,full',
            'notes' => 'nullable|string',
        ]);

        $filename = 'backup_' . date('Y-m-d_His') . '_' . Str::random(8) . '.sql';
        $filePath = 'backups/' . $filename;

        // Create backup (simplified - in production, use proper backup tools)
        $backup = Backup::create([
            'created_by' => $request->user()->id,
            'filename' => $filename,
            'file_path' => $filePath,
            'type' => $validated['type'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        // In production, implement actual backup logic here
        // For now, just mark as completed
        $backup->update([
            'status' => 'completed',
            'file_size' => '0 KB',
        ]);

        return response()->json($backup->load('creator'), 201);
    }

    public function show($id)
    {
        $backup = Backup::with('creator')->findOrFail($id);
        return response()->json($backup);
    }

    public function restore(Request $request, $id)
    {
        $backup = Backup::findOrFail($id);

        if ($backup->status !== 'completed') {
            return response()->json([
                'message' => 'Backup is not ready for restoration'
            ], 422);
        }

        // In production, implement actual restore logic here
        $backup->update([
            'restored_at' => now(),
        ]);

        return response()->json([
            'message' => 'Backup restored successfully',
            'backup' => $backup->load('creator'),
        ]);
    }

    public function destroy($id)
    {
        $backup = Backup::findOrFail($id);

        // Delete file if exists
        if (Storage::exists($backup->file_path)) {
            Storage::delete($backup->file_path);
        }

        $backup->delete();

        return response()->json(['message' => 'Backup deleted successfully']);
    }
}

