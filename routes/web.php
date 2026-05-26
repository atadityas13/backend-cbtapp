<?php
 
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\BanController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Web\LandingController;
use Illuminate\Support\Facades\Route;
 
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
 
// 1. Halaman Beranda (Landing Page & Uji User-Agent Siswa)
Route::get('/', [LandingController::class, 'index'])->name('landing');
 
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
 
    // Pengiriman Notifikasi Push Manual (Firebase Cloud Messaging)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/send', [NotificationController::class, 'send'])->name('notifications.send');
 
    // Pengaturan Sistem Ujian, Proktor Ganda, & Iklan
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/update', [SettingsController::class, 'updateSettings'])->name('settings.update');
    
    // Kelola Proktor (Hanya untuk Role Admin/Super Admin)
    Route::middleware(['can:manage-proctors'])->group(function () {
        Route::post('/settings/proctors', [SettingsController::class, 'addProctor'])->name('proctors.store');
        Route::delete('/settings/proctors/{id}', [SettingsController::class, 'deleteProctor'])->name('proctors.destroy');
    });
});
