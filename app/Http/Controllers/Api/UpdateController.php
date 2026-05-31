<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function checkUpdate(Request $request)
    {
        $appId    = $request->input('app_id', '');
        $platform = $request->input('platform', 'android');

        $downloadUrl = Setting::getValue('download_url', 'https://play.google.com/store/apps/details?id=com.mtsn11.cbtapp');
        $forceUpdate = Setting::getValue('force_update', true);

        // ── Platform Windows: skema kompatibel aplikasi Windows legacy ────────
        if ($platform === 'windows') {
            $windowsVersions = [
                'CBT-App'         => ['version' => '1.0.1', 'description' => "- Fitur cek update otomatis\n- Perbaikan UI dan UX\n- Optimasi performa aplikasi"],
                'CBT-App-MTsN11'  => ['version' => '1.0.0', 'description' => "- Fitur cek update otomatis\n- Perbaikan UI dan UX\n- Optimasi performa aplikasi"],
            ];

            if (isset($windowsVersions[$appId])) {
                return response()->json([
                    'version'      => $windowsVersions[$appId]['version'],
                    'description'  => $windowsVersions[$appId]['description'],
                    'downloadUrl'  => $downloadUrl,
                    'force_update' => (bool) $forceUpdate,
                ]);
            }

            return response()->json(['error' => 'Invalid app ID for Windows platform'], 400);
        }

        $latestVersion     = Setting::getValue('latest_version', '4.2.5');
        $latestVersionCode = intval(Setting::getValue('latest_version_code', 8));
        $releaseNotes      = Setting::getValue(
            'release_notes',
            "Pembaruan Sistem v4.2.5:\n\n• 🛠️ Kirim Bantuan Proktor: Siswa kini bisa mengajukan keluhan/bantuan langsung melalui tombol melayang pintar di pojok kanan bawah layar ujian.\n• 🔔 Sinyal Visual Pintar: Indikator ikon dinamis (Pending/Answered), glow ring hijau, dan badge merah notifikasi saat ada balasan baru dari proktor.\n• 🛡️ Proteksi Double-Submit: Keamanan ganda di tingkat frontend & backend untuk mencegah pengiriman jawaban ganda secara tidak sengaja.\n• 📋 Riwayat Bantuan Terpusat: Seluruh tiket bantuan yang selesai otomatis diarsipkan dan dapat dipantau di dashboard admin proktor.\n• ⚡ Smart Polling Dashboard: Sinkronisasi real-time antrean bantuan yang otomatis dijeda saat proktor sedang mengetik balasan agar penginputan tidak terganggu."
        );

        return response()->json([
            'latest_version'      => $latestVersion,
            'latest_version_code' => $latestVersionCode,
            'release_notes'       => $releaseNotes,
            'download_url'        => $downloadUrl,
            'force_update'        => (bool) $forceUpdate,
        ]);
    }
}
