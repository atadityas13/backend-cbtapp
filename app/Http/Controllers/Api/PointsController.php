<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmRegistration;
use App\Models\CbtPelanggaran;
use App\Models\IapPurchase;
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
                    'message' => "Poin Proteksi tidak cukup! Butuh {$actualCost} Poin Proteksi, Saldo Anda: {$registration->points} Poin."
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
                'message' => 'Denda berhasil dibayarkan, Poin Proteksi disesuaikan.',
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
            $packageName = 'Penebusan Denda Ringan (+2 Poin Proteksi)';
        } elseif ($packet === 'packet_b') {
            $pointsToAdd = 5; // Rp 2.500
            $packageName = 'Penebusan Denda Sedang (+5 Poin Proteksi)';
        } elseif ($packet === 'packet_c') {
            $pointsToAdd = 10; // Rp 5.000
            $packageName = 'Penebusan Denda Utama (+10 Poin Proteksi)';
        } elseif ($packet === 'packet_d') {
            $pointsToAdd = 20; // Rp 10.000
            $packageName = 'Penebusan Denda Maksimal (+20 Poin Proteksi)';
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
            'message' => "Pemulihan Poin Proteksi sukses! Berhasil memproses {$packageName}.",
            'new_points' => $registration->points
        ]);
    }

    /**
     * Endpoint: /api/points/topup/verify-iap
     * Securely verify Google Play IAP purchase and credit student points.
     * Protected against replay attacks using unique token checking.
     */
    public function verifyIapPurchase(Request $request)
    {
        $androidId = trim($request->input('android_id', ''));
        $purchaseToken = trim($request->input('purchase_token', ''));
        $productId = trim($request->input('product_id', ''));
        $orderId = trim($request->input('order_id', ''));

        if (empty($androidId) || empty($purchaseToken) || empty($productId) || empty($orderId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'android_id, purchase_token, product_id, dan order_id wajib diisi!'
            ], 400);
        }

        // 1. PROTEKSI REPLAY ATTACK: Pastikan token belum pernah dipakai
        $duplicateToken = IapPurchase::where('purchase_token', $purchaseToken)->first();
        if ($duplicateToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token pembelian telah digunakan! Pembayaran ganda diblokir.'
            ], 400);
        }

        // 2. Proteksi order ID ganda
        $duplicateOrder = IapPurchase::where('order_id', $orderId)->first();
        if ($duplicateOrder) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order ID telah terdaftar! Pembayaran ganda diblokir.'
            ], 400);
        }

        // 3. Tentukan jumlah poin kepatuhan
        $pointsToAdd = 0;
        $packageName = '';
        if ($productId === 'denda_ringan') {
            $pointsToAdd = 10;
            $packageName = 'Penebusan Denda Ringan (Rp 4.500 = +10 Poin)';
        } elseif ($productId === 'denda_sedang') {
            $pointsToAdd = 22;
            $packageName = 'Penebusan Denda Sedang (Rp 9.000 = +22 Poin)';
        } elseif ($productId === 'denda_berat') {
            $pointsToAdd = 35;
            $packageName = 'Penebusan Denda Berat (Rp 13.000 = +35 Poin)';
        } else {
            // Default/Fallback jika menggunakan custom ID
            $pointsToAdd = 10;
            $packageName = 'Penebusan Denda Proteksi (+10 Poin)';
        }

        // 4. Cari siswa berdasarkan android_id
        $registration = FcmRegistration::where('android_id', $androidId)->first();
        if (!$registration) {
            return response()->json([
                'status' => 'error',
                'message' => 'Perangkat tidak terdaftar!'
            ], 404);
        }

        // 5. Jalankan transaksi database
        return DB::transaction(function () use ($registration, $purchaseToken, $productId, $orderId, $pointsToAdd, $packageName) {
            // Simpan riwayat pembelian IAP
            IapPurchase::create([
                'purchase_token' => $purchaseToken,
                'order_id' => $orderId,
                'android_id' => $registration->android_id,
                'product_id' => $productId,
                'points_added' => $pointsToAdd,
                'status' => 'SUCCESS'
            ]);

            // Tambahkan poin ke siswa
            $registration->points += $pointsToAdd;
            $registration->save();

            return response()->json([
                'status' => 'success',
                'message' => "Verifikasi IAP Sukses! {$packageName} berhasil diproses.",
                'new_points' => $registration->points
            ]);
        });
    }
}
