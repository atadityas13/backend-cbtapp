<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CbtBantuanProktor;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    /**
     * Endpoint: /api/help/send
     * Send a new help request from student to proctor
     */
    public function sendHelp(Request $request)
    {
        $androidId = trim($request->input('android_id', ''));
        $username = trim($request->input('username', ''));
        $namaSiswa = trim($request->input('nama_siswa', ''));
        $pesanSiswa = trim($request->input('pesan_siswa', ''));

        if (empty($androidId) || empty($username) || empty($namaSiswa) || empty($pesanSiswa)) {
            return response()->json([
                'status' => 'error',
                'message' => 'android_id, username, nama_siswa, dan pesan_siswa wajib diisi!'
            ], 400);
        }

        try {
            // Check if there is already an active request (PENDING or ANSWERED)
            $activeRequest = CbtBantuanProktor::where('android_id', $androidId)
                ->whereIn('status', ['PENDING', 'ANSWERED'])
                ->first();

            if ($activeRequest) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal: Anda masih memiliki tiket bantuan aktif yang belum diselesaikan.'
                ], 400);
            }

            CbtBantuanProktor::create([
                'android_id'  => $androidId,
                'username'    => $username,
                'nama_siswa'  => $namaSiswa,
                'pesan_siswa' => $pesanSiswa,
                'status'      => 'PENDING'
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Pesan bantuan Anda berhasil dikirim ke Proktor.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengirim pesan bantuan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint: /api/help/status
     * Check current help request status for a student
     */
    public function checkHelpStatus(Request $request)
    {
        $androidId = trim($request->input('android_id', ''));

        if (empty($androidId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'android_id wajib diisi!'
            ], 400);
        }

        try {
            $activeRequest = CbtBantuanProktor::where('android_id', $androidId)
                ->whereIn('status', ['PENDING', 'ANSWERED'])
                ->latest()
                ->first();

            if (!$activeRequest) {
                return response()->json([
                    'status'  => 'idle',
                    'message' => 'Tidak ada bantuan aktif.'
                ]);
            }

            if ($activeRequest->status === 'PENDING') {
                return response()->json([
                    'status'  => 'pending',
                    'message' => 'Menunggu balasan dari Proktor.'
                ]);
            }

            return response()->json([
                'status' => 'answered',
                'reply'  => $activeRequest->balasan_proktor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengecek status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint: /api/help/confirm
     * Confirm that the help reply has been read and resolved by the student, unlocking a new request
     */
    public function confirmHelpResolved(Request $request)
    {
        $androidId = trim($request->input('android_id', ''));

        if (empty($androidId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'android_id wajib diisi!'
            ], 400);
        }

        try {
            $activeRequest = CbtBantuanProktor::where('android_id', $androidId)
                ->whereIn('status', ['PENDING', 'ANSWERED'])
                ->latest()
                ->first();

            if (!$activeRequest) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Tidak ada bantuan aktif untuk diselesaikan.'
                ]);
            }

            $activeRequest->update([
                'status' => 'RESOLVED'
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Status bantuan berhasil diselesaikan. Anda sekarang dapat mengirim bantuan baru.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyelesaikan status bantuan: ' . $e->getMessage()
            ], 500);
        }
    }
}
