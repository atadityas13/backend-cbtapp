<?php
 
use App\Http\Controllers\Api\UpdateController;
use App\Http\Controllers\Api\FcmController;
use App\Http\Controllers\Api\ViolationController;
use Illuminate\Support\Facades\Route;
 
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
 
// 1. Pengecekan Versi Update
Route::post('/check_update.php', [UpdateController::class, 'checkUpdate']);
 
// 2. Registrasi & Manajemen Perangkat (FCM)
Route::post('/notifikasi/simpan_token.php', [FcmController::class, 'simpanToken']);
Route::post('/notifikasi/check_android_id.php', [FcmController::class, 'checkAndroidId']);
 
// 3. Pelaporan Pelanggaran & Pemulihan Ban
Route::post('/notifikasi/report_violation.php', [ViolationController::class, 'reportViolation']);
Route::post('/notifikasi/resolve_ban.php', [ViolationController::class, 'resolveBan']);

// 4. Manajemen Poin Proteksi & Penebusan Denda
Route::post('/points/spend', [\App\Http\Controllers\Api\PointsController::class, 'spendPoints']);
Route::post('/points/topup', [\App\Http\Controllers\Api\PointsController::class, 'topupPoints']);
Route::post('/points/topup/verify-iap', [\App\Http\Controllers\Api\PointsController::class, 'verifyIapPurchase']);

// 5. Sistem Bantuan Siswa-Proktor
Route::post('/help/send', [\App\Http\Controllers\Api\HelpController::class, 'sendHelp']);
Route::post('/help/status', [\App\Http\Controllers\Api\HelpController::class, 'checkHelpStatus']);
Route::post('/help/confirm', [\App\Http\Controllers\Api\HelpController::class, 'confirmHelpResolved']);


