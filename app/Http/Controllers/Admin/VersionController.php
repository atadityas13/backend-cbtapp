<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class VersionController extends Controller
{
    /**
     * Display the version settings page
     */
    public function index()
    {
        $settings = [
            'latest_version'      => Setting::getValue('latest_version', '4.2.3'),
            'latest_version_code' => intval(Setting::getValue('latest_version_code', 6)),
            'download_url'        => Setting::getValue('download_url', 'https://play.google.com/store/apps/details?id=com.mtsn11.cbtapp'),
            'release_notes'       => Setting::getValue('release_notes', ''),
            'force_update'        => (bool) Setting::getValue('force_update', false),
        ];

        return view('admin.versions.index', compact('settings'));
    }

    /**
     * Update the version settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'latest_version'      => 'required|string|max:50',
            'latest_version_code' => 'required|integer|min:1',
            'download_url'        => 'required|url|max:1000',
            'release_notes'       => 'required|string',
            'force_update'        => 'nullable|boolean',
        ]);

        Setting::setValue('latest_version', $validated['latest_version']);
        Setting::setValue('latest_version_code', $validated['latest_version_code']);
        Setting::setValue('download_url', $validated['download_url']);
        Setting::setValue('release_notes', $validated['release_notes']);
        Setting::setValue('force_update', $request->has('force_update'));

        return redirect()->route('admin.versions.index')->with('success', 'Manajemen Versi Aplikasi berhasil diperbarui.');
    }
}
