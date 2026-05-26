<?php
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Models\CbtPelanggaran;
use Illuminate\Http\Request;
 
class ViolationController extends Controller
{
    /**
     * Endpoint: /api/notifikasi/report_violation.php
     * Log a student violation and trigger a BANNED status
     */
    public function reportViolation(Request $request)
    {
        $studentName = $request->input('student_name');
        $fcmToken = $request->input('fcm_token');
        $reason = $request->input('reason');
        $duration = intval($request->input('duration_minutes', 15));
        $deviceModel = $request->input('device_model');
        $androidVersion = $request->input('android_version');
 
        if (empty($studentName) || empty($fcmToken) || empty($reason)) {
            return response()->json([
                'status' => 'error',
                'message' => 'student_name, fcm_token, dan reason wajib diisi!'
            ], 400);
        }
 
        try {
            CbtPelanggaran::create([
                'student_name'     => $studentName,
                'fcm_token'        => $fcmToken,
                'reason'           => $reason,
                'duration_minutes' => $duration,
                'device_model'     => $deviceModel,
                'android_version'  => $androidVersion,
                'status'           => 'BANNED'
            ]);
 
            // Original response is 200 OK (empty or small JSON)
            return response()->json([
                'status' => 'success',
                'message' => 'Laporan pelanggaran berhasil disimpan.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan laporan: ' . $e->getMessage()
            ], 500);
        }
    }
 
    /**
     * Endpoint: /api/notifikasi/resolve_ban.php
     * Lift a student's ban by changing status from BANNED to UNBANNED
     */
    public function resolveBan(Request $request)
    {
        $fcmToken = $request->input('fcm_token');
 
        if (empty($fcmToken)) {
            return response()->json([
                'status' => 'error',
                'message' => 'fcm_token wajib diisi!'
            ], 400);
        }
 
        try {
            // Find all active bans for this token and lift them
            CbtPelanggaran::where('fcm_token', $fcmToken)
                ->where('status', 'BANNED')
                ->update(['status' => 'UNBANNED']);
 
            return response()->json([
                'status' => 'success',
                'message' => 'Status hukuman siswa berhasil dibuka.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuka status hukuman: ' . $e->getMessage()
            ], 500);
        }
    }
}
