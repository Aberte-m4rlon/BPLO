<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Bplo\ApplicationController as BploApplicationController;
use App\Http\Controllers\Bplo\DashboardController as BploDashboardController;
use App\Http\Controllers\Bplo\PaymentController as BploPaymentController;
use App\Http\Controllers\Bplo\PermitController as BploPermitController;
use App\Http\Controllers\PublicPages\VerificationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/bplo/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate'])
        ->middleware('throttle:login');

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:bplo'])->prefix('bplo')->name('bplo.')->group(function (): void {
    Route::get('/dashboard', [BploDashboardController::class, 'index'])->name('dashboard');

    Route::get('/applications', [BploApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}', [BploApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/review', [BploApplicationController::class, 'beginReview'])->name('applications.review');
    Route::post('/applications/{application}/approve', [BploApplicationController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{application}/return', [BploApplicationController::class, 'returnForCorrection'])->name('applications.return');
    Route::post('/applications/{application}/reject', [BploApplicationController::class, 'reject'])->name('applications.reject');
    Route::post('/applications/{application}/requirements/{req}/flag', [BploApplicationController::class, 'flagRequirement'])->name('applications.requirements.flag');
    Route::get('/requirements/{applicationRequirement}/file', [BploApplicationController::class, 'file'])->name('requirements.file');

    Route::get('/permits', [BploPermitController::class, 'index'])->name('permits.index');
    Route::get('/payments/{payment}/receipt', [BploPaymentController::class, 'proof'])->name('payments.proof');
    Route::post('/applications/{application}/release', [BploPermitController::class, 'release'])->name('applications.release');
    Route::post('/permits/{permit}/regenerate', [BploPermitController::class, 'regenerate'])->name('permits.regenerate');
});

Route::get('/verify', [VerificationController::class, 'lookup'])->name('verify.lookup');
Route::get('/verify/{token}', [VerificationController::class, 'show'])
    ->middleware('throttle:verify')
    ->name('verify.show');
