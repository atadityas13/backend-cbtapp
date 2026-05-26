<?php
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
 
class UpdateController extends Controller
{
    public function checkUpdate(Request $request)
    {
        $appId = $request->input('app_id', '');
        $platform = $request->input('platform', '');
 
        // Default values if settings not seeded/configured yet
        $latestVersion = Setting::getValue('latest_version', '4.2.3');
        $releaseNotes = Setting::getValue('release_notes', "Pembaruan Sistem v4.2.3:\n\n• Peningkatan perizinan Do Not Disturb (DND).\n• Keamanan sistem proktor baru.");
        $downloadUrl = Setting::getValue('download_url', 'https://mtsn11majalengka.sch.id/download');
        $forceUpdate = Setting::getValue('force_update', true);
 
        // Return identical structure
        return response()->json([
            'latest_version' => $latestVersion,
            'release_notes'  => $releaseNotes,
            'download_url'   => $downloadUrl,
            'force_update'   => (bool) $forceUpdate,
        ]);
    }
}
