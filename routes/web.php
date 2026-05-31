<?php
 
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\BanController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ScheduledNotificationController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\VersionController;
use App\Http\Controllers\Web\LandingController;
use Illuminate\Support\Facades\Route;
 
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
 
// 1. Halaman Beranda (Gerbang Utama — Cek Operasional & User-Agent)
Route::get('/', [LandingController::class, 'index'])->name('landing');

// 1a. Terminal Verifikasi Keamanan (Retro CLI + Screen Pinning)
Route::get('/verify-security', [LandingController::class, 'verifySecurity'])->name('verify-security');

// 1b. Portal Pemilihan Akses Asesmen (Sumatif & Madrasah)
Route::get('/portal', [LandingController::class, 'portal'])->name('portal');
 
// 2. Autentikasi Admin & Proktor
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
 
// 3. Area Dashboard Terproteksi (Wajib Login)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Halaman Dashboard Utama
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
 
    // Manajemen Registrasi Siswa (Satu HP Satu Nama)
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::post('/students/{id}/update', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
 
    // Manajemen Pelanggaran & Lepas Ban (Pardon)
    Route::get('/violations', [BanController::class, 'index'])->name('violations.index');
    Route::post('/violations/{id}/unban', [BanController::class, 'unban'])->name('violations.unban');
    Route::post('/violations/bulk-unban', [BanController::class, 'bulkUnban'])->name('violations.bulk-unban');

    // Manajemen Bantuan / Keluhan Siswa (Real-Time Help System)
    Route::get('/help', [\App\Http\Controllers\Admin\HelpAdminController::class, 'index'])->name('help.index');
    Route::post('/help/{id}/reply', [\App\Http\Controllers\Admin\HelpAdminController::class, 'reply'])->name('help.reply');
    Route::post('/help/{id}/resolve', [\App\Http\Controllers\Admin\HelpAdminController::class, 'resolve'])->name('help.resolve');
 
    // Manajemen File Media (Khusus Super Admin)
    Route::middleware(['can:manage-proctors'])->group(function () {
        Route::get('/media', [MediaController::class, 'index'])->name('media.index');
        Route::post('/media/delete', [MediaController::class, 'destroy'])->name('media.destroy');
    });
 
    // Pengiriman Notifikasi Push Manual (Firebase Cloud Messaging)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/send', [NotificationController::class, 'send'])->name('notifications.send');
    
    // Pengiriman Notifikasi Push Terjadwal
    Route::get('/notifications/scheduled', [ScheduledNotificationController::class, 'index'])->name('notifications.scheduled.index');
    Route::post('/notifications/scheduled', [ScheduledNotificationController::class, 'store'])->name('notifications.scheduled.store');
    Route::post('/notifications/scheduled/{id}/toggle', [ScheduledNotificationController::class, 'toggleActive'])->name('notifications.scheduled.toggle');
    Route::delete('/notifications/scheduled/{id}', [ScheduledNotificationController::class, 'destroy'])->name('notifications.scheduled.destroy');
 
    // Pengaturan Sistem Ujian & Kredensial Akun
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
    
    // Manajemen Pembaruan Versi Aplikasi (Khusus Super Admin)
    Route::middleware(['can:manage-proctors'])->group(function () {
        Route::get('/versions', [VersionController::class, 'index'])->name('versions.index');
        Route::post('/versions/update', [VersionController::class, 'update'])->name('versions.update');
    });
    
    // Kelola Proktor & Pengaturan Sistem (Khusus Super Admin)
    Route::middleware(['can:manage-proctors'])->group(function () {
        Route::post('/settings/update', [SettingsController::class, 'updateSettings'])->name('settings.update');
        Route::post('/settings/proctors', [SettingsController::class, 'addProctor'])->name('proctors.store');
        Route::delete('/settings/proctors/{id}', [SettingsController::class, 'deleteProctor'])->name('proctors.destroy');
    });
});
