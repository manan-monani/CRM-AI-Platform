<?php

use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Deals
    Route::get('deals', [App\Http\Controllers\Customer\CustomerDealController::class, 'index'])->name('deals.index');
    Route::get('deals/create', [App\Http\Controllers\Customer\CustomerDealController::class, 'create'])->name('deals.create');
    Route::post('deals', [App\Http\Controllers\Customer\CustomerDealController::class, 'store'])->name('deals.store');
    Route::get('deals/{deal}', [App\Http\Controllers\Customer\CustomerDealController::class, 'show'])->name('deals.show');

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\Customer\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/recent', [App\Http\Controllers\Customer\NotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\Customer\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [App\Http\Controllers\Customer\NotificationController::class, 'markAllAsRead'])->name('notifications.read_all');
    Route::delete('/notifications/{id}', [App\Http\Controllers\Customer\NotificationController::class, 'destroy'])->name('notifications.destroy');
});
