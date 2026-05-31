<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CbtBantuanProktor;
use App\Models\FcmRegistration;
use Illuminate\Http\Request;

class HelpAdminController extends Controller
{
    /**
     * Display all active help tickets (PENDING or ANSWERED)
     */
    public function index(Request $request)
    {
        $helps = CbtBantuanProktor::whereIn('status', ['PENDING', 'ANSWERED'])
            ->orderBy('status', 'asc') // PENDING first
            ->orderBy('updated_at', 'desc')
            ->get();

        // Jika dipanggil via AJAX polling dari dashboard (smart polling),
        // kembalikan JSON ringkas saja — tanpa memuat ulang halaman
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'pending_count' => $helps->where('status', 'PENDING')->count(),
                'total_count'   => $helps->count(),
            ]);
        }

        return view('admin.help.index', compact('helps'));
    }

    /**
     * Send reply from Proctor to student and push FCM
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'balasan_proktor' => 'required|string|max:500'
        ]);

        $help = CbtBantuanProktor::findOrFail($id);

        $help->update([
            'balasan_proktor' => $request->balasan_proktor,
            'status'          => 'ANSWERED'
        ]);

        // Find FCM Token for this student
        $registration = FcmRegistration::where('android_id', $help->android_id)->first();
        $fcmToken = $registration ? $registration->fcm_token : null;
        $fcmMessage = '';

        if (!empty($fcmToken)) {
            $serviceAccountPath = base_path('service-account.json');
            if (!file_exists($serviceAccountPath)) {
                $legacyPath = base_path('../apkcbt.mtsn11majalengka.sch.id/administrator/service-account.json');
                if (file_exists($legacyPath)) {
                    $serviceAccountPath = $legacyPath;
                }
            }

            if (file_exists($serviceAccountPath) && class_exists('\Kreait\Firebase\Factory')) {
                try {
                    $factory = (new \Kreait\Firebase\Factory)->withServiceAccount($serviceAccountPath);
                    $messaging = $factory->createMessaging();

                    $message = \Kreait\Firebase\Messaging\CloudMessage::fromArray([
                        'token' => $fcmToken,
                        'data' => [
                            'action' => 'HELP_ANSWERED',
                            'reply'  => $request->balasan_proktor
                        ]
                    ]);

                    $messaging->send($message);
                    $fcmMessage = ' (Notifikasi FCM berhasil dikirim ke perangkat siswa)';
                } catch (\Throwable $e) {
                    $fcmMessage = ' (FCM gagal dikirim: ' . $e->getMessage() . ')';
                }
            } else {
                $fcmMessage = ' (FCM gagal: berkas service-account.json tidak ditemukan)';
            }
        } else {
            $fcmMessage = ' (FCM tidak dikirim karena Token FCM tidak terdaftar)';
        }

        return redirect()->route('admin.help.index')->with('success', "Jawaban berhasil dikirim ke '{$help->nama_siswa}'" . $fcmMessage);
    }

    /**
     * Manually mark a help ticket as RESOLVED and push FCM to reset student's state
     */
    public function resolve($id)
    {
        $help = CbtBantuanProktor::findOrFail($id);

        $help->update([
            'status' => 'RESOLVED'
        ]);

        // Send FCM to student device to unlock their help button immediately
        $registration = FcmRegistration::where('android_id', $help->android_id)->first();
        $fcmToken = $registration ? $registration->fcm_token : null;
        $fcmMessage = '';

        if (!empty($fcmToken)) {
            $serviceAccountPath = base_path('service-account.json');
            if (!file_exists($serviceAccountPath)) {
                $legacyPath = base_path('../apkcbt.mtsn11majalengka.sch.id/administrator/service-account.json');
                if (file_exists($legacyPath)) {
                    $serviceAccountPath = $legacyPath;
                }
            }

            if (file_exists($serviceAccountPath) && class_exists('\Kreait\Firebase\Factory')) {
                try {
                    $factory = (new \Kreait\Firebase\Factory)->withServiceAccount($serviceAccountPath);
                    $messaging = $factory->createMessaging();

                    $message = \Kreait\Firebase\Messaging\CloudMessage::fromArray([
                        'token' => $fcmToken,
                        'data' => [
                            'action' => 'HELP_RESOLVED'
                        ]
                    ]);

                    $messaging->send($message);
                    $fcmMessage = ' (Notifikasi penyelesaian FCM berhasil dikirim)';
                } catch (\Throwable $e) {
                    $fcmMessage = ' (FCM penyelesaian gagal dikirim: ' . $e->getMessage() . ')';
                }
            }
        }

        return redirect()->route('admin.help.index')->with('success', "Tiket bantuan '{$help->nama_siswa}' berhasil diselesaikan." . $fcmMessage);
    }
}
