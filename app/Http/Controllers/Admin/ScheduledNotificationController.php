<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FcmRegistration;
use App\Models\CbtScheduledNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ScheduledNotificationController extends Controller
{
    /**
     * Display the scheduled notifications view & current schedules
     */
    public function index()
    {
        // Get all unique topics for the dropdown
        $topics = FcmRegistration::distinct()->pluck('topic')->toArray();
        if (empty($topics)) {
            $topics = ['cbt_notif'];
        }

        // Get all registered students
        $students = FcmRegistration::orderBy('full_name', 'asc')->get();

        // Get all current scheduled notifications
        $schedules = CbtScheduledNotification::orderBy('schedule_time', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $laravelBase = public_path('uploads');

        // Retrieve uploaded image list
        $uploadedImageList = [];
        if (File::exists($laravelBase)) {
            $files = File::files($laravelBase);
            foreach ($files as $file) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $uploadedImageList[] = $file->getFilename();
                }
            }
        }

        // Retrieve uploaded audio list
        $uploadedAudioList = [];
        $audioDir = $laravelBase . '/audio';
        if (File::exists($audioDir)) {
            $files = File::files($audioDir);
            foreach ($files as $file) {
                if (strtolower($file->getExtension()) === 'mp3') {
                    $uploadedAudioList[] = $file->getFilename();
                }
            }
        }

        return view('admin.notifications.scheduled', compact(
            'topics',
            'students',
            'schedules',
            'uploadedImageList',
            'uploadedAudioList'
        ));
    }

    /**
     * Store a new scheduled notification schedule
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'               => 'required|string|max:255',
            'deskripsi'           => 'required|string',
            'topik'               => 'nullable|string',
            'fcm_token'           => 'nullable|string',
            'gambar_url'          => 'nullable|string',
            'link'                => 'nullable|string',
            'prioritas'           => 'required|in:high,normal',
            'custom_sound'        => 'nullable|array',
            'custom_sound.*'      => 'string',
            'category'            => 'required|string|max:50',
            'duration'            => 'nullable|integer|min:5|max:300',
            'schedule_time'       => 'required|string|regex:/^\d{2}:\d{2}$/',
            'days_of_week'        => 'required|array',
            'days_of_week.*'      => 'string',
        ]);

        $customSounds = $validated['custom_sound'] ?? [];
        if (!is_array($customSounds)) {
            $customSounds = [];
        }

        // 1. Logika Upload Audio (Jika Ada) - Tambahkan ke daftar suara kustom
        if ($request->hasFile('audio_file') && $request->file('audio_file')->isValid()) {
            $audioFile = $request->file('audio_file');
            $safeName = time() . "_voice_" . preg_replace("/[^a-zA-Z0-9\._-]/", "_", $audioFile->getClientOriginalName());
            $audioFile->move(public_path('uploads/audio'), $safeName);
            $customSounds[] = asset('uploads/audio/' . $safeName);
        }

        // Jika tidak ada audio terpilih sama sekali, pasang default
        if (empty($customSounds)) {
            $customSounds = ['default'];
        }

        // 2. Logika Upload Gambar (Jika Ada)
        $gambarUrl = $validated['gambar_url'] ?? '';
        if (empty($gambarUrl) && $request->hasFile('gambar_file') && $request->file('gambar_file')->isValid()) {
            $imageFile = $request->file('gambar_file');
            $safeName = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "_", $imageFile->getClientOriginalName());
            $imageFile->move(public_path('uploads'), $safeName);
            $gambarUrl = asset('uploads/' . $safeName);
        }

        // Create scheduled notification schedule
        CbtScheduledNotification::create([
            'judul'         => $validated['judul'],
            'deskripsi'     => $validated['deskripsi'],
            'topik'         => $validated['topik'] ?? null,
            'fcm_token'     => $validated['fcm_token'] ?? null,
            'gambar_url'    => $gambarUrl ?: null,
            'link'          => $validated['link'] ?? null,
            'prioritas'     => $validated['prioritas'],
            'custom_sound'  => $customSounds, // Auto-casted to JSON array!
            'category'      => $validated['category'],
            'duration'      => intval($validated['duration'] ?? 30),
            'schedule_time' => $validated['schedule_time'],
            'days_of_week'  => $validated['days_of_week'],
            'is_active'     => true,
        ]);

        return redirect()->route('admin.notifications.scheduled.index')
            ->with('success', 'Jadwal notifikasi berhasil dibuat.');
    }

    /**
     * Toggle the active status of a schedule
     */
    public function toggleActive($id)
    {
        $schedule = CbtScheduledNotification::findOrFail($id);
        $schedule->update([
            'is_active' => !$schedule->is_active
        ]);

        $status = $schedule->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('admin.notifications.scheduled.index')
            ->with('success', "Jadwal berhasil {$status}.");
    }

    /**
     * Delete a schedule from database
     */
    public function destroy($id)
    {
        $schedule = CbtScheduledNotification::findOrFail($id);
        $schedule->delete();

        return redirect()->route('admin.notifications.scheduled.index')
            ->with('success', 'Jadwal notifikasi berhasil dihapus.');
    }
}
