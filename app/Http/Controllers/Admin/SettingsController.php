<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    /**
     * Display the settings page
     */
    public function index()
    {
        // General settings
        $settings = [
            'operational_start_date'  => Setting::getValue('operational_start_date', '2026-05-25 00:00:00'),
            'operational_end_date'    => Setting::getValue('operational_end_date', '2026-06-05 23:59:59'),
            'daily_start_hour'        => Setting::getValue('daily_start_hour', '7'),
            'daily_start_minute'      => Setting::getValue('daily_start_minute', '25'),
            'daily_end_hour'          => Setting::getValue('daily_end_hour', '12'),
            'daily_end_minute'        => Setting::getValue('daily_end_minute', '30'),
            'asesmen_sumatif_active'  => (bool) Setting::getValue('asesmen_sumatif_active', true),
            'asesmen_madrasah_active' => (bool) Setting::getValue('asesmen_madrasah_active', false),
            'ad_show'                 => (bool) Setting::getValue('ad_show', true),
            'ad_image'                => Setting::getValue('ad_image', ''),
            'ad_author'               => Setting::getValue('ad_author', ''),
            'ad_time'                 => Setting::getValue('ad_time', 3),
        ];

        // Fetch proctors (only visible/manageable if user is admin/superadmin, but we pass it anyway)
        $proctors = User::where('role', 'proktor')->orderBy('name', 'asc')->get();

        return view('admin.settings.index', compact('settings', 'proctors'));
    }

    /**
     * Update the general system configurations
     */
    public function updateSettings(Request $request)
    {
        // Convert datetime-local format (YYYY-MM-DDTHH:MM) into database standard (YYYY-MM-DD HH:MM:SS)
        if ($request->has('operational_start_date') && !empty($request->input('operational_start_date'))) {
            $request->merge([
                'operational_start_date' => date('Y-m-d H:i:s', strtotime($request->input('operational_start_date'))),
            ]);
        }
        if ($request->has('operational_end_date') && !empty($request->input('operational_end_date'))) {
            $request->merge([
                'operational_end_date' => date('Y-m-d H:i:s', strtotime($request->input('operational_end_date'))),
            ]);
        }

        // Split daily_start_time (HH:MM) into daily_start_hour and daily_start_minute
        if ($request->has('daily_start_time') && !empty($request->input('daily_start_time'))) {
            $parts = explode(':', $request->input('daily_start_time'));
            $request->merge([
                'daily_start_hour' => intval($parts[0] ?? 0),
                'daily_start_minute' => intval($parts[1] ?? 0),
            ]);
        }
        if ($request->has('daily_end_time') && !empty($request->input('daily_end_time'))) {
            $parts = explode(':', $request->input('daily_end_time'));
            $request->merge([
                'daily_end_hour' => intval($parts[0] ?? 0),
                'daily_end_minute' => intval($parts[1] ?? 0),
            ]);
        }

        $validated = $request->validate([
            'operational_start_date'  => 'required|date_format:Y-m-d H:i:s',
            'operational_end_date'    => 'required|date_format:Y-m-d H:i:s|after:operational_start_date',
            'daily_start_hour'        => 'required|integer|min:0|max:23',
            'daily_start_minute'      => 'required|integer|min:0|max:59',
            'daily_end_hour'          => 'required|integer|min:0|max:23',
            'daily_end_minute'        => 'required|integer|min:0|max:59',
            'asesmen_sumatif_active'  => 'nullable|boolean',
            'asesmen_madrasah_active' => 'nullable|boolean',
            'ad_show'                 => 'nullable|boolean',
            'ad_image'                => 'nullable|string|max:1000',
            'ad_author'               => 'nullable|string|max:255',
            'ad_time'                 => 'required|integer|min:1|max:60',
        ]);

        // Process file upload for Ad Image if provided
        $adImageUrl = $validated['ad_image'] ?? '';
        if ($request->hasFile('ad_image_file') && $request->file('ad_image_file')->isValid()) {
            $imageFile = $request->file('ad_image_file');
            $safeName = time() . "_ad_" . preg_replace("/[^a-zA-Z0-9\._-]/", "_", $imageFile->getClientOriginalName());
            $imageFile->move(public_path('uploads'), $safeName);
            $adImageUrl = asset('uploads/' . $safeName);
        }

        // Save values
        Setting::setValue('operational_start_date', $validated['operational_start_date']);
        Setting::setValue('operational_end_date', $validated['operational_end_date']);
        Setting::setValue('daily_start_hour', $validated['daily_start_hour']);
        Setting::setValue('daily_start_minute', $validated['daily_start_minute']);
        Setting::setValue('daily_end_hour', $validated['daily_end_hour']);
        Setting::setValue('daily_end_minute', $validated['daily_end_minute']);
        Setting::setValue('asesmen_sumatif_active', $request->has('asesmen_sumatif_active'));
        Setting::setValue('asesmen_madrasah_active', $request->has('asesmen_madrasah_active'));
        Setting::setValue('ad_show', $request->has('ad_show'));
        Setting::setValue('ad_image', $adImageUrl);
        Setting::setValue('ad_author', $validated['ad_author'] ?? '');
        Setting::setValue('ad_time', $validated['ad_time']);

        return redirect()->route('admin.settings.index')->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }

    /**
     * Add a new proctor user
     */
    public function addProctor(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ]);

        User::create([
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role'     => 'proktor',
        ]);

        return redirect()->route('admin.settings.index')->with('success', 'Akun Proktor baru berhasil dibuat.');
    }

    /**
     * Delete a proctor user
     */
    public function deleteProctor($id)
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'proktor') {
            return redirect()->route('admin.settings.index')->with('error', 'Anda tidak dapat menghapus akun administrator utama.');
        }

        $user->delete();

        return redirect()->route('admin.settings.index')->with('success', 'Akun Proktor berhasil dihapus.');
    }

    /**
     * Update the logged-in user's own profile credentials
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user->name = $validated['name'];
        $user->username = $validated['username'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.settings.index')->with('success', 'Kredensial akun Anda berhasil diperbarui.');
    }
}
