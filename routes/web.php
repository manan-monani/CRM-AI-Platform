<?php

use App\Http\Controllers\Guest\AuthController;
use App\Http\Controllers\Guest\GoogleAuthController;
use App\Http\Controllers\Guest\HomeController;
use App\Http\Controllers\Guest\LeadGenerationController;
use App\Http\Controllers\Guest\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Landing Page & Lead Generation
Route::get('/lead-generation', [LeadGenerationController::class, 'index'])->name('leadGeneration');
Route::post('/lead-generation', [LeadGenerationController::class, 'storeLead'])->name('leadGeneration.store');

// Google OAuth
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'storeLogin']);
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'storeRegister']);
});

Route::post('logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::get('/privacy-policy', [App\Http\Controllers\Guest\LegalPageController::class, 'show'])->defaults('slug', 'privacy-policy')->name('legal.privacy');
Route::get('/terms-and-conditions', [App\Http\Controllers\Guest\LegalPageController::class, 'show'])->defaults('slug', 'terms-and-conditions')->name('legal.terms');
Route::get('/about-us', [App\Http\Controllers\Guest\LegalPageController::class, 'show'])->defaults('slug', 'about-us')->name('legal.about');
// Contact Us
Route::get('/contact-us', [App\Http\Controllers\Guest\ContactController::class, 'show'])->name('contact');

// Public Lead Forms
Route::get('/form/{slug}', [App\Http\Controllers\PublicLeadFormController::class, 'show'])->name('public.lead-form.show');
Route::post('/form/{slug}', [App\Http\Controllers\PublicLeadFormController::class, 'store'])->name('public.lead-form.store');

// Dashboard fallback or redirect based on role could be handled here if needed.
