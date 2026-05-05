<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\SettingsController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────
Route::post('/auth/login', [AuthController::class, 'login']);

// Stripe webhook — must be outside auth middleware (Stripe signs it differently)
Route::post('/stripe/webhook', [InvoiceController::class, 'stripeWebhook']);

// ── Authenticated ─────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // ── Admin: Owner management ───────────────────────────────────────────────
    Route::get('/owners',            [AuthController::class, 'listOwners']);
    Route::post('/owners',           [AuthController::class, 'createOwner']);
    Route::delete('/owners/{owner}', [AuthController::class, 'deleteOwner']);

    // ── Dashboard ─────────────────────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // ── Leads ─────────────────────────────────────────────────────────────────
    Route::get('/leads/stats',           [LeadController::class, 'stats']);
    Route::post('/leads/{lead}/convert', [LeadController::class, 'convert']);
    Route::apiResource('leads', LeadController::class);

    // ── Customers ─────────────────────────────────────────────────────────────
    Route::get('/customers/stats',      [CustomerController::class, 'stats']);
    Route::get('/customers',            [CustomerController::class, 'index']);
    Route::get('/customers/{customer}', [CustomerController::class, 'show']);

    // ── Jobs ──────────────────────────────────────────────────────────────────
    Route::get('/jobs/stats',  [JobController::class, 'stats']);
    Route::get('/jobs/kanban', [JobController::class, 'kanban']);
    Route::apiResource('jobs', JobController::class);

    // ── Invoices ──────────────────────────────────────────────────────────────
    Route::get('/invoices/stats',                    [InvoiceController::class, 'stats']);
    Route::post('/invoices/{invoice}/checkout',      [InvoiceController::class, 'stripeCheckout']);
    Route::apiResource('invoices', InvoiceController::class)->except(['store']);

    // ── Settings ──────────────────────────────────────────────────────────────
    Route::get('/settings/profile',              [SettingsController::class, 'getProfile']);
    Route::put('/settings/profile',              [SettingsController::class, 'updateProfile']);
    Route::get('/settings/preferences',          [SettingsController::class, 'getPreferences']);
    Route::put('/settings/preferences',          [SettingsController::class, 'updatePreferences']);
    Route::get('/settings/team',                 [SettingsController::class, 'getTeam']);
    Route::post('/settings/team',                [SettingsController::class, 'addTeamMember']);
    Route::put('/settings/team/{member}',        [SettingsController::class, 'updateTeamMember']);
    Route::delete('/settings/team/{member}',     [SettingsController::class, 'deleteTeamMember']);
});