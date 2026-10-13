<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\CallLogController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\LeadImportController;
use App\Http\Controllers\RoleController;

// Public & Authentication Routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated & Active CRM Routes
Route::middleware(['auth', 'active_user'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Leads Import from Excel/CSV
    Route::get('/leads-import', [LeadImportController::class, 'show'])->name('leads.import.show');
    Route::get('/leads-import/template', [LeadImportController::class, 'downloadTemplate'])->name('leads.import.template');
    Route::post('/leads-import', [LeadImportController::class, 'import'])->name('leads.import.process');

    // Leads Management
    Route::resource('leads', LeadController::class);
    Route::post('/leads/{lead}/assign', [LeadController::class, 'assign'])
        ->name('leads.assign')
        ->middleware('super_admin');

    // Cold Calling Logs & Status Updates
    Route::post('/leads/{lead}/call-logs', [CallLogController::class, 'store'])
        ->name('call-logs.store');

    // Meetings Management & Alerts
    Route::get('/meetings', [MeetingController::class, 'index'])->name('meetings.index');
    Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
    Route::patch('/meetings/{meeting}/status', [MeetingController::class, 'updateStatus'])->name('meetings.update-status');

    // Deals Won & Projects Showcase
    Route::get('/deals', [DealController::class, 'index'])->name('deals.index');
    Route::get('/deals/{deal}', [DealController::class, 'show'])->name('deals.show');
    Route::post('/deals', [DealController::class, 'store'])->name('deals.store');

    // Daily Automated Activity Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/generate', [ReportController::class, 'generateNow'])
        ->name('reports.generate')
        ->middleware('super_admin');

    // Super Admin Exclusive: Team & Roles Management
    Route::middleware('super_admin')->group(function () {
        Route::get('/team', [TeamController::class, 'index'])->name('team.index');
        Route::post('/team', [TeamController::class, 'store'])->name('team.store');
        Route::patch('/team/{user}/toggle-status', [TeamController::class, 'toggleStatus'])->name('team.toggle-status');
        Route::patch('/team/{user}/role', [TeamController::class, 'updateRole'])->name('team.update-role');

        // Dynamic Roles & Permissions CRUD
        Route::resource('roles', RoleController::class);
    });
});
