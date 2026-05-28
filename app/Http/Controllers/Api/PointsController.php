<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmRegistration;
use App\Models\CbtPelanggaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PointsController extends Controller
{
    /**
     * Endpoint: /api/points/spend
     * Securely spend student points for services on the server
     */
    public function spendPoints(Request $request)
    {
        $androidId = trim($request->input('android_id', ''));
        $actionType = trim($request->input('action_type', ''));
        $pointsCost = intval($request->input('points_cost', 0));

        if (empty($androidId) || empty($actionType)) {
            return response()->json([
                'status' => 'error',
                'message' => 'android_id dan action_type wajib diisi!'
            ], 400);
        }

        // Jalankan transaksi database untuk integritas data
        return DB::transaction(function () use ($androidId, $actionType, $pointsCost) {
            $registration = FcmRegistration::where('android_id', $androidId)->lockForUpdate()->first();

            if (!$registration) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Perangkat tidak terdaftar!'
                ], 404);
            }

            // Validasi Biaya Aksi Server-Side
            $actualCost = 0;
            if ($actionType === 'mute_alarm_lifetime') {
                $actualCost = 12;
            } elseif ($actionType === 'unban_3m') {
                $actualCost = 1;
            } elseif ($actionType === 'unban_5m') {
                $actualCost = 2;
            } elseif ($actionType === 'unban_10m') {
                $actualCost = 4;
            } elseif ($actionType === 'unban_15m') {
                $actualCost = 6;
            } elseif ($actionType === 'exit_app') {
                $actualCost = $pointsCost; // Sesuai biaya progresif yang dilaporkan client
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tipe aksi tidak valid!'
                ], 400);
            }

            // Validasi Saldo Poin
            if ($registration->points < $actualCost) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Poin Kepatuhan tidak cukup! Butuh {$actualCost} Poin Kepatuhan, Saldo Anda: {$registration->points} Poin."
                ], 400);
            }

            // Proteksi Hard Lock oleh Proktor untuk aksi Unban
            if (str_starts_with($actionType, 'unban_')) {
                $activeBan = CbtPelanggaran::where('fcm_token', $registration->fcm_token)
                    ->where('status', 'BANNED')
                    ->latest()
                    ->first();

                if (!$activeBan) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Tidak ada hukuman Ban aktif!'
                    ], 400);
                }

                if ($activeBan->is_hard_lock) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Gagal: Akses perangkat ini dikunci total (Hard Lock) oleh Proktor! Penebusan denda dinonaktifkan.'
                    ], 403);
                }

                // Proses pemotongan waktu ban secara aman
                $minutesToReduce = 0;
                if ($actionType === 'unban_3m') $minutesToReduce = 3;
                elseif ($actionType === 'unban_5m') $minutesToReduce = 5;
                elseif ($actionType === 'unban_10m') $minutesToReduce = 10;
                elseif ($actionType === 'unban_15m') $minutesToReduce = 15;

                $newDuration = max(0, $activeBan->duration_minutes - $minutesToReduce);
                $activeBan->duration_minutes = $newDuration;

                if ($newDuration <= 0) {
                    $activeBan->status = 'UNBANNED';
                }
                $activeBan->save();
            }

            // Kurangi poin & update status khusus
            $registration->points -= $actualCost;
            if ($actionType === 'mute_alarm_lifetime') {
                $registration->alarm_muted_lifetime = true;
            }
            $registration->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Denda berhasil dibayarkan, Poin Kepatuhan disesuaikan.',
                'new_points' => $registration->points,
                'alarm_muted_lifetime' => (bool)$registration->alarm_muted_lifetime
            ]);
        });
    }

    /**
     * Endpoint: /api/points/topup
     * Simulated top-up packets endpoint for testing
     */
    public function topupPoints(Request $request)
    {
        $androidId = trim($request->input('android_id', ''));
        $packet = trim($request->input('packet', '')); // "packet_a", "packet_b", "packet_c", "packet_d"

        if (empty($androidId) || empty($packet)) {
            return response()->json([
                'status' => 'error',
                'message' => 'android_id dan paket penebusan denda wajib diisi!'
            ], 400);
        }

        $pointsToAdd = 0;
        $packageName = '';
        if ($packet === 'packet_a') {
            $pointsToAdd = 2; // Rp 1.000
            $packageName = 'Penebusan Denda Ringan (+2 Poin Kepatuhan)';
        } elseif ($packet === 'packet_b') {
            $pointsToAdd = 5; // Rp 2.500
            $packageName = 'Penebusan Denda Sedang (+5 Poin Kepatuhan)';
        } elseif ($packet === 'packet_c') {
            $pointsToAdd = 10; // Rp 5.000
            $packageName = 'Penebusan Denda Utama (+10 Poin Kepatuhan)';
        } elseif ($packet === 'packet_d') {
            $pointsToAdd = 20; // Rp 10.000
            $packageName = 'Penebusan Denda Maksimal (+20 Poin Kepatuhan)';
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Paket penebusan denda tidak dikenal!'
            ], 400);
        }

        $registration = FcmRegistration::where('android_id', $androidId)->first();

        if (!$registration) {
            return response()->json([
                'status' => 'error',
                'message' => 'Perangkat tidak terdaftar!'
            ], 404);
        }

        $registration->points += $pointsToAdd;
        $registration->save();

        return response()->json([
            'status' => 'success',
            'message' => "Pemulihan Poin Kepatuhan sukses! Berhasil memproses {$packageName}.",
            'new_points' => $registration->points
        ]);
    }
}
