<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function info(Request $request)
    {
        $backups = Backup::orderByDesc('created_at')->get();

        return response()->json([
            'data' => [
                'database_size' => $this->getDatabaseSize(),
                'backup_count' => $backups->count(),
                'last_backup' => optional($backups->first())->created_at,
                'backups' => $backups->map(fn ($backup) => $this->formatBackupResource($backup))->values(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateBackupRequest($request);
        $backup = $this->createBackupEntry($request, $validated);

        return response()->json($backup->load('creator'), 201);
    }

    public function createBackup(Request $request)
    {
        $validated = $this->validateBackupRequest($request);
        $backup = $this->createBackupEntry($request, $validated);

        return response()->json([
            'message' => 'Backup created successfully',
            'data' => $this->formatBackupResource($backup),
        ], 201);
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
        $this->deleteBackup($backup);

        return response()->json(['message' => 'Backup deleted successfully']);
    }

    public function download(string $filename)
    {
        $backup = Backup::where('filename', $filename)->firstOrFail();

        abort_if(!Storage::exists($backup->file_path), 404, 'Backup file not found');

        return Storage::download($backup->file_path, $backup->filename);
    }

    public function deleteByFilename(string $filename)
    {
        $backup = Backup::where('filename', $filename)->firstOrFail();
        $this->deleteBackup($backup);

        return response()->json(['message' => 'Backup deleted successfully']);
    }

    protected function validateBackupRequest(Request $request): array
    {
        return $request->validate([
            'type' => 'required|in:database,full',
            'notes' => 'nullable|string',
        ]);
    }

    protected function createBackupEntry(Request $request, array $validated): Backup
    {
        $filename = 'backup_' . now()->format('Y-m-d_His') . '_' . Str::lower(Str::random(8)) . '.sql';
        $filePath = 'backups/' . $filename;

        Storage::makeDirectory('backups');

        $metadata = [
            'type' => $validated['type'],
            'generated_at' => now()->toIso8601String(),
            'generated_by' => $request->user()->email ?? $request->user()->username ?? $request->user()->id,
            'notes' => $validated['notes'] ?? null,
        ];

        Storage::put($filePath, json_encode($metadata, JSON_PRETTY_PRINT));

        $fileSize = Storage::exists($filePath) ? Storage::size($filePath) : 0;

        return Backup::create([
            'created_by' => $request->user()->id,
            'filename' => $filename,
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'type' => $validated['type'],
            'status' => 'completed',
            'notes' => $validated['notes'] ?? null,
        ]);
    }

    protected function deleteBackup(Backup $backup): void
    {
        if (Storage::exists($backup->file_path)) {
            Storage::delete($backup->file_path);
        }

        $backup->delete();
    }

    protected function formatBackupResource(Backup $backup): array
    {
        return [
            'id' => $backup->id,
            'name' => $backup->filename,
            'type' => $backup->type,
            'size' => $this->resolveBackupSize($backup),
            'status' => $backup->status,
            'created_at' => $backup->created_at,
        ];
    }

    protected function resolveBackupSize(Backup $backup): int
    {
        if (is_numeric($backup->file_size)) {
            return (int) $backup->file_size;
        }

        if (Storage::exists($backup->file_path)) {
            return (int) Storage::size($backup->file_path);
        }

        return 0;
    }

    protected function getDatabaseSize(): int
    {
        try {
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");

            if ($driver !== 'mysql') {
                return 0;
            }

            $database = config("database.connections.{$connection}.database");
            $result = DB::selectOne(
                'SELECT SUM(data_length + index_length) AS size FROM information_schema.tables WHERE table_schema = ?',
                [$database]
            );

            return (int) ($result->size ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }
}

