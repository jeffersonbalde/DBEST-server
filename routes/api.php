<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PropertyCustodianAuthController;
use App\Http\Controllers\Auth\TeacherAuthController;
use App\Http\Controllers\Auth\IctAuthController;
use App\Http\Controllers\Auth\AccountingAuthController;
use App\Http\Controllers\InventoryCategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\AssignedItemController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\InventoryReportController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\PropertyCustodianController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\DcpPackageController;
use App\Http\Controllers\PropertyCustodianDcpPackageController;
use App\Http\Controllers\DcpInventoryController;
use App\Http\Controllers\PropertyCustodianSchoolProfileController;
use App\Models\DcpPackage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

// Public routes
Route::prefix('auth')->group(function () {
    // Unified Login - automatically detects user type
    Route::post('login', [\App\Http\Controllers\Auth\UnifiedAuthController::class, 'login']);

    // Individual register routes (for admin use)
    Route::post('property-custodian/register', [PropertyCustodianAuthController::class, 'register']);
    Route::post('teacher/register', [TeacherAuthController::class, 'register']);
    Route::post('ict/register', [IctAuthController::class, 'register']);
    Route::post('accounting/register', [AccountingAuthController::class, 'register']);
});

// Property Custodian Routes
Route::middleware('auth:sanctum')->prefix('property-custodian')->group(function () {
    Route::get('user', [PropertyCustodianAuthController::class, 'user']);
    Route::post('logout', [PropertyCustodianAuthController::class, 'logout']);
    Route::post('profile', [PropertyCustodianAuthController::class, 'updateProfile']);
    Route::put('settings/change-password', [PropertyCustodianAuthController::class, 'changePassword']);

    // Inventory Management
    Route::apiResource('inventory', InventoryController::class);
    Route::post('inventory/{id}/generate-ics', [InventoryController::class, 'generateICS']);
    // Use a distinct prefix to avoid route-model binding conflicts with `inventory`
    // Register the list route BEFORE apiResource to avoid route conflicts
    Route::get('inventory-categories/list', [InventoryCategoryController::class, 'dropdown']);
    Route::apiResource('inventory-categories', InventoryCategoryController::class);

    // Assigned Items
    Route::apiResource('assigned-items', AssignedItemController::class);

    // Personnel Management
    Route::apiResource('personnel', PersonnelController::class);
    Route::patch('personnel/{id}/activate', [PersonnelController::class, 'activate']);
    Route::patch('personnel/{id}/deactivate', [PersonnelController::class, 'deactivate']);

    // Reports
    Route::apiResource('reports', InventoryReportController::class);
    Route::post('reports/generate', [InventoryReportController::class, 'generate']);

    // DCP Packages (per school visibility)
    Route::get('dcp-packages', [PropertyCustodianDcpPackageController::class, 'index']);
    Route::put('dcp-packages/{dcpPackage}', [PropertyCustodianDcpPackageController::class, 'update']);

    // DCP Inventory (per school visibility)
    Route::apiResource('dcp-inventory', DcpInventoryController::class)
        ->parameters(['dcp-inventory' => 'dcpInventoryItem'])
        ->only([
            'index',
            'store',
            'update',
            'destroy',
        ]);

    // School Profile (linked to the property custodian's assigned school)
    Route::get('school-profile', [PropertyCustodianSchoolProfileController::class, 'show']);
    Route::post('school-profile', [PropertyCustodianSchoolProfileController::class, 'update']);
});

// Teacher Routes
Route::middleware('auth:sanctum')->prefix('teacher')->group(function () {
    Route::get('user', [TeacherAuthController::class, 'user']);
    Route::post('logout', [TeacherAuthController::class, 'logout']);
    Route::put('settings/change-password', [TeacherAuthController::class, 'changePassword']);

    // Manage Personnel Details (their own)
    Route::get('personnel/{id}', [PersonnelController::class, 'show'])->where('id', 'me');
    Route::match(['put', 'post'], 'personnel/{id}', [PersonnelController::class, 'update'])->where('id', 'me');

    // View Assigned Items
    Route::get('assigned-items', [AssignedItemController::class, 'index']);
    Route::get('assigned-items/{id}', [AssignedItemController::class, 'show']);
});

// ICT Routes
Route::middleware('auth:sanctum')->prefix('ict')->group(function () {
    Route::get('user', [IctAuthController::class, 'user']);
    Route::post('logout', [IctAuthController::class, 'logout']);

    // System Settings
    Route::put('settings/change-password', [IctAuthController::class, 'changePassword']);
    Route::apiResource('settings', SystemSettingController::class);

    // Backup and Restore
    Route::apiResource('backups', BackupController::class);
    Route::post('backups/{id}/restore', [BackupController::class, 'restore']);

    // Manage Property Custodian Accounts
    Route::apiResource('property-custodians', PropertyCustodianController::class);
    Route::patch('property-custodians/{id}/activate', [PropertyCustodianController::class, 'activate']);
    Route::patch('property-custodians/{id}/deactivate', [PropertyCustodianController::class, 'deactivate']);

    // Manage Accounting Accounts
    Route::apiResource('accountings', AccountingController::class);
    Route::patch('accountings/{id}/activate', [AccountingController::class, 'activate']);
    Route::patch('accountings/{id}/deactivate', [AccountingController::class, 'deactivate']);

    // DepEd Schools Registry
    Route::apiResource('schools', SchoolController::class)->only(['index', 'store', 'update', 'destroy']);

    // DCP Packages
    Route::apiResource('dcp-packages', DcpPackageController::class);
});

Route::middleware('auth:sanctum')->prefix('backup')->group(function () {
    Route::get('info', [BackupController::class, 'info']);
    Route::post('create', [BackupController::class, 'createBackup']);
    Route::get('download/{filename}', [BackupController::class, 'download']);
    Route::delete('delete/{filename}', [BackupController::class, 'deleteByFilename']);
});

// Accounting Routes
Route::middleware('auth:sanctum')->prefix('accounting')->group(function () {
    Route::get('user', [AccountingAuthController::class, 'user']);
    Route::post('logout', [AccountingAuthController::class, 'logout']);

    // View Inventory Analytics
    Route::get('analytics', [AnalyticsController::class, 'getInventoryAnalytics']);
    Route::get('analytics/financial', [AnalyticsController::class, 'getFinancialAnalytics']);

    // View Detailed Inventory List
    Route::get('inventory', [AnalyticsController::class, 'getDetailedInventoryList']);
});

// School Avatar Route - Similar to your staff avatar route
Route::get('/school-avatar/{filename}', function ($filename) {
    // Clean the filename - remove any "school-avatars/" prefix if present
    $cleanFilename = str_replace('school-avatars/', '', $filename);

    $path = 'school-avatars/' . $cleanFilename;

    if (!Storage::disk('public')->exists($path)) {
        Log::error("School avatar not found: " . $path);
        abort(404);
    }

    $file = Storage::disk('public')->get($path);

    // Get mime type
    $mimeType = mime_content_type(storage_path('app/public/' . $path));

    return response($file, 200)
        ->header('Content-Type', $mimeType)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Cross-Origin-Resource-Policy', 'cross-origin');
});

// Custodian Avatar Route
Route::get('/custodian-avatar/{filename}', function ($filename) {
    // Clean the filename - remove any "avatars/" prefix if present
    $cleanFilename = str_replace('avatars/', '', $filename);

    $path = 'avatars/' . $cleanFilename;

    if (!Storage::disk('public')->exists($path)) {
        Log::error("Custodian avatar not found: " . $path);
        abort(404);
    }

    $file = Storage::disk('public')->get($path);

    // Get mime type
    $mimeType = mime_content_type(storage_path('app/public/' . $path));

    return response($file, 200)
        ->header('Content-Type', $mimeType)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Cross-Origin-Resource-Policy', 'cross-origin');
});

// Accounting Avatar Route
Route::get('/accounting-avatar/{filename}', function ($filename) {
    // Clean the filename - remove any "avatars/" prefix if present
    $cleanFilename = str_replace('avatars/', '', $filename);

    $path = 'avatars/' . $cleanFilename;

    if (!Storage::disk('public')->exists($path)) {
        Log::error("Accounting avatar not found: " . $path);
        abort(404);
    }

    $file = Storage::disk('public')->get($path);

    // Get mime type
    $mimeType = mime_content_type(storage_path('app/public/' . $path));

    return response($file, 200)
        ->header('Content-Type', $mimeType)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Cross-Origin-Resource-Policy', 'cross-origin');
});

// Personnel Avatar Route
Route::get('/personnel-avatar/{filename}', function ($filename) {
    $cleanFilename = str_replace('personnel-avatars/', '', $filename);
    $path = 'personnel-avatars/' . $cleanFilename;

    if (!Storage::disk('public')->exists($path)) {
        Log::error("Personnel avatar not found: " . $path);
        abort(404);
    }

    $file = Storage::disk('public')->get($path);
    $mimeType = mime_content_type(storage_path('app/public/' . $path));

    return response($file, 200)
        ->header('Content-Type', $mimeType)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Cross-Origin-Resource-Policy', 'cross-origin');
});

// Inventory Asset Image Route
Route::get('/inventory-asset/{filename}', function ($filename) {
    $cleanFilename = str_replace('inventory-assets/', '', $filename);
    $path = 'inventory-assets/' . $cleanFilename;

    if (!Storage::disk('public')->exists($path)) {
        Log::error("Inventory asset image not found: " . $path);
        abort(404);
    }

    $file = Storage::disk('public')->get($path);
    $mimeType = mime_content_type(storage_path('app/public/' . $path));

    return response($file, 200)
        ->header('Content-Type', $mimeType)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Cross-Origin-Resource-Policy', 'cross-origin');
});

// DCP Package document download route
Route::get('/dcp-package-file/{dcpPackage}/{type}', function (DcpPackage $dcpPackage, string $type) {
    $type = strtolower($type);
    if (!in_array($type, ['dr', 'ptr', 'iar'], true)) {
        abort(404);
    }

    $filenameField = "{$type}_filename";
    $filename = $dcpPackage->{$filenameField};

    if (!$filename) {
        Log::warning("DCP document missing filename", [
            'package_id' => $dcpPackage->id,
            'type' => $type,
        ]);
        abort(404);
    }

    $path = "dcp-documents/{$type}/{$filename}";

    if (!Storage::disk('public')->exists($path)) {
        Log::error("DCP document not found", [
            'package_id' => $dcpPackage->id,
            'type' => $type,
            'path' => $path,
        ]);
        abort(404);
    }

    $file = Storage::disk('public')->get($path);
    $mimeType = mime_content_type(storage_path('app/public/' . $path));

    return response($file, 200)
        ->header('Content-Type', $mimeType)
        ->header('Content-Disposition', 'attachment; filename="'.$filename.'"')
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Cross-Origin-Resource-Policy', 'cross-origin');
});
