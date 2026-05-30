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

        // ── Platform Android: skema standar ───────────────────────────────────
        $latestVersion     = Setting::getValue('latest_version', '4.2.4');
        $latestVersionCode = intval(Setting::getValue('latest_version_code', 7));
        $releaseNotes      = Setting::getValue(
            'release_notes',
            "Pembaruan Sistem v4.2.4:\n\n• Perbaikan bug laporan pelanggaran dikirim 2x ke server.\n• Informasi model HP & versi Android kini tersimpan otomatis.\n• Stabilitas dan optimasi sistem."
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
