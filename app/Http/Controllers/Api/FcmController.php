<?php
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Models\FcmRegistration;
use Illuminate\Http\Request;
 
class FcmController extends Controller
{
    /**
     * Endpoint: /api/notifikasi/simpan_token.php
     * Save/register FCM token with strict unique Android ID constraints
     */
    public function simpanToken(Request $request)
    {
        $fcmToken = $request->input('fcm_token');
        $fullName = trim($request->input('full_name', ''));
        $androidId = trim($request->input('android_id', ''));
        $topic = $request->input('topic', 'cbt_notif');
 
        if (empty($fcmToken) || empty($fullName) || empty($androidId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'FCM Token, Nama Lengkap, dan Android ID wajib diisi!'
            ], 400);
        }
 
        // 1. Validasi Batasan Unik Android ID (1 HP = 1 Nama)
        $existingDevice = FcmRegistration::where('android_id', $androidId)->first();
 
        if ($existingDevice) {
            // Jika nama berbeda, blokir registrasi untuk mencegah joki/ganti akun
            if (strcasecmp($existingDevice->full_name, $fullName) !== 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Gagal: Perangkat ini sudah terdaftar atas nama: '{$existingDevice->full_name}'. Satu HP hanya boleh mendaftar 1 Nama Siswa!"
                ], 400);
            }
 
            // Jika nama sama, update token FCM (jika berubah akibat reinstall/clear data)
            $existingDevice->update([
                'fcm_token' => $fcmToken,
                'topic' => $topic
            ]);
 
            return response()->json([
                'status' => 'success',
                'message' => 'Token FCM berhasil diperbarui.'
            ]);
        }
 
        // 2. Auto-Migrasi: Tangani rekor lama jika android_id bernilai NULL
        $legacyToken = FcmRegistration::where('fcm_token', $fcmToken)->first();
        if ($legacyToken) {
            if (empty($legacyToken->android_id)) {
                // Pasangkan Android ID secara sah jika rekor lama belum memilikinya
                $legacyToken->update([
                    'android_id' => $androidId,
                    'full_name' => $fullName,
                    'topic' => $topic
                ]);
 
                return response()->json([
                    'status' => 'success',
                    'message' => 'Registrasi perangkat legacy berhasil dimigrasikan.'
                ]);
            }
        }
 
        // 3. Registrasi Perangkat Baru
        try {
            FcmRegistration::create([
                'fcm_token' => $fcmToken,
                'full_name' => $fullName,
                'android_id' => $androidId,
                'topic' => $topic
            ]);
 
            return response()->json([
                'status' => 'success',
                'message' => 'Perangkat dan nama siswa berhasil didaftarkan.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mendaftarkan ke database: ' . $e->getMessage()
            ], 500);
        }
    }
 
    /**
     * Endpoint: /api/notifikasi/check_android_id.php
     * Check if the device's Android ID is registered in the database
     */
    public function checkAndroidId(Request $request)
    {
        $androidId = trim($request->input('android_id', ''));
 
        if (empty($androidId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Android ID wajib dikirim!'
            ], 400);
        }
 
        $registration = FcmRegistration::where('android_id', $androidId)->first();
 
        if ($registration) {
            return response()->json([
                'status' => 'registered',
                'full_name' => $registration->full_name
            ]);
        }
 
        return response()->json([
            'status' => 'not_registered'
        ]);
    }
}
