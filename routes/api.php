<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PropertyCustodianAuthController;
use App\Http\Controllers\Auth\TeacherAuthController;
use App\Http\Controllers\Auth\IctAuthController;
use App\Http\Controllers\Auth\AccountingAuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\AssignedItemController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\InventoryReportController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\PropertyCustodianController;
use App\Http\Controllers\AnalyticsController;

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
    
    // Inventory Management
    Route::apiResource('inventory', InventoryController::class);
    Route::get('inventory/categories/list', [InventoryController::class, 'getCategories']);
    
    // Assigned Items
    Route::apiResource('assigned-items', AssignedItemController::class);
    
    // Personnel Management
    Route::apiResource('personnel', PersonnelController::class);
    
    // Reports
    Route::apiResource('reports', InventoryReportController::class);
    Route::post('reports/generate', [InventoryReportController::class, 'generate']);
});

// Teacher Routes
Route::middleware('auth:sanctum')->prefix('teacher')->group(function () {
    Route::get('user', [TeacherAuthController::class, 'user']);
    Route::post('logout', [TeacherAuthController::class, 'logout']);
    
    // Manage Personnel Details (their own)
    Route::get('personnel/me', [PersonnelController::class, 'show']);
    Route::put('personnel/me', [PersonnelController::class, 'update']);
    
    // View Assigned Items
    Route::get('assigned-items', [AssignedItemController::class, 'index']);
    Route::get('assigned-items/{id}', [AssignedItemController::class, 'show']);
});

// ICT Routes
Route::middleware('auth:sanctum')->prefix('ict')->group(function () {
    Route::get('user', [IctAuthController::class, 'user']);
    Route::post('logout', [IctAuthController::class, 'logout']);
    
    // System Settings
    Route::apiResource('settings', SystemSettingController::class);
    
    // Backup and Restore
    Route::apiResource('backups', BackupController::class);
    Route::post('backups/{id}/restore', [BackupController::class, 'restore']);
    
    // Manage Property Custodian Accounts
    Route::apiResource('property-custodians', PropertyCustodianController::class);
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

