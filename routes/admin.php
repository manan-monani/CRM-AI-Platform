<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DealController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\LeadSourceController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\ReminderController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class , 'showLogin'])->name('login');
    Route::post('login', [AuthController::class , 'storeLogin']);
    Route::get('register', [AuthController::class , 'showRegister'])->name('register');
    Route::post('register', [AuthController::class , 'storeRegister']);
});

Route::middleware(['auth:admin', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('logout', [AuthController::class , 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class , 'index'])->name('dashboard');

    Route::resource('users', UserController::class);
    Route::patch('users/{user}/status', [UserController::class , 'updateStatus'])->name('users.status');

    Route::get('/branding', [BrandingController::class , 'index'])->name('branding.index');
    Route::put('/branding/{brand}', [BrandingController::class , 'update'])->name('branding.update');

    Route::resource('roles', RoleController::class);

    Route::get('/activity-logs', [ActivityLogController::class , 'index'])->name('activity_logs.index');
    Route::get('/activity-logs/{activityLog}', [ActivityLogController::class , 'show'])->name('activity_logs.show');

    Route::get('/profile', [App\Http\Controllers\Admin\ProfileController::class , 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\Admin\ProfileController::class , 'update'])->name('profile.update');

    Route::get('/business/branding', [App\Http\Controllers\Admin\BusinessSettingController::class , 'index'])->name('business.branding');
    Route::post('/business/branding', [App\Http\Controllers\Admin\BusinessSettingController::class , 'update'])->name('business.update');

    Route::get('/business/legal', [App\Http\Controllers\Admin\LegalController::class , 'index'])->name('legal.index');
    Route::post('/business/legal', [App\Http\Controllers\Admin\LegalController::class , 'update'])->name('legal.update');

    Route::get('/business/settings', [App\Http\Controllers\Admin\BusinessLogicController::class , 'index'])->name('business.settings.index');
    Route::post('/business/settings', [App\Http\Controllers\Admin\BusinessLogicController::class , 'update'])->name('business.settings.update');

    Route::get('/business/lead-generation-page', [App\Http\Controllers\Admin\BusinessSettingController::class , 'pageBuilder'])->name('business.lead-generation-page');
    Route::post('/business/lead-generation-page', [App\Http\Controllers\Admin\BusinessSettingController::class , 'updatePageBuilder'])->name('business.lead-generation-page.update');

    Route::get('/business/home-page-builder', [App\Http\Controllers\Admin\BusinessSettingController::class , 'homePageBuilder'])->name('business.home-page-builder');
    Route::post('/business/home-page-builder', [App\Http\Controllers\Admin\BusinessSettingController::class , 'updateHomePageBuilder'])->name('business.home-page-builder.update');

    // CRM - Customers
    Route::resource('customers', CustomerController::class);

    // CRM - Leads
    Route::resource('leads', LeadController::class);
    Route::patch('leads/{lead}/status', [LeadController::class , 'updateStatus'])->name('leads.status');
    Route::post('leads/{lead}/convert', [LeadController::class , 'convertToCustomer'])->name('leads.convert');

    // Lead Sources
    Route::get('lead-sources', [LeadSourceController::class , 'index'])->name('lead-sources.index');
    Route::post('lead-sources', [LeadSourceController::class , 'store'])->name('lead-sources.store');

    // Lead Forms
    Route::patch('lead-generation-form/{lead_form}/default', [App\Http\Controllers\Admin\LeadFormController::class , 'setDefault'])->name('lead-generation-form.default');
    Route::resource('lead-generation-form', App\Http\Controllers\Admin\LeadFormController::class)->parameters([
        'lead-generation-form' => 'lead_form',
    ])->names('lead-generation-form');

    // CRM - Deals
    Route::resource('deals', DealController::class);
    Route::patch('deals/{deal}/stage', [DealController::class , 'updateStage'])->name('deals.stage');

    // CRM - Reminders
    Route::resource('reminders', ReminderController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('reminders/{reminder}/snooze', [ReminderController::class , 'snooze'])->name('reminders.snooze');
    Route::post('reminders/{reminder}/dismiss', [ReminderController::class , 'dismiss'])->name('reminders.dismiss');

    // CRM - Activities
    Route::get('activities', [ActivityController::class , 'index'])->name('activities.index');
    Route::post('activities', [ActivityController::class , 'store'])->name('activities.store');
    Route::match (['put', 'patch'], 'activities/{activity}', [ActivityController::class , 'update'])->name('activities.update');
    Route::delete('activities/{activity}', [ActivityController::class , 'destroy'])->name('activities.destroy');

    // CRM - Tasks
    Route::resource('tasks', TaskController::class);
    Route::patch('tasks/{task}/complete', [TaskController::class , 'complete'])->name('tasks.complete');

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\Admin\NotificationController::class , 'index'])->name('notifications.index');
    Route::get('/notifications/recent', [App\Http\Controllers\Admin\NotificationController::class , 'recent'])->name('notifications.recent');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\Admin\NotificationController::class , 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [App\Http\Controllers\Admin\NotificationController::class , 'markAllAsRead'])->name('notifications.read_all');
    Route::delete('/notifications/{id}', [App\Http\Controllers\Admin\NotificationController::class , 'destroy'])->name('notifications.destroy');

    // Media
    Route::post('media', [MediaController::class , 'store'])->name('media.store');
    Route::delete('media/{media}', [MediaController::class , 'destroy'])->name('media.destroy');
});