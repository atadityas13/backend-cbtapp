<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\CbtPelanggaran;
use Illuminate\Http\Request;
 
class BanController extends Controller
{
    /**
     * Display a listing of violations (with search and status filter)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status', 'BANNED'); // Default focus on active BANNED
 
        $query = CbtPelanggaran::query();
 
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhere('device_model', 'like', "%{$search}%");
            });
        }
 
        if ($statusFilter !== 'ALL') {
            $query->where('status', $statusFilter);
        }
 
        $violations = $query->orderBy('created_at', 'desc')->paginate(15);
 
        return view('admin.violations.index', compact('violations', 'search', 'statusFilter'));
    }
 
    /**
     * Manually lift a student's ban (Pardon)
     */
    public function unban($id)
    {
        $violation = CbtPelanggaran::findOrFail($id);
 
        $violation->update([
            'status' => 'UNBANNED'
        ]);

        // Kirim perintah FCM "UNBAN_STUDENT" ke perangkat siswa agar kunci otomatis terbuka
        $fcmToken = $violation->fcm_token;
        $fcmMessage = '';

        if (!empty($fcmToken)) {
            $serviceAccountPath = base_path('service-account.json');
            if (!file_exists($serviceAccountPath)) {
                $legacyPath = base_path('../apkcbt.mtsn11majalengka.sch.id/administrator/service-account.json');
                if (file_exists($legacyPath)) {
                    $serviceAccountPath = $legacyPath;
                }
            }

            if (file_exists($serviceAccountPath)) {
                try {
                    $factory = (new \Kreait\Firebase\Factory)->withServiceAccount($serviceAccountPath);
                    $messaging = $factory->createMessaging();

                    $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $fcmToken)
                        ->withData([
                            'action' => 'UNBAN_STUDENT'
                        ]);

                    $messaging->send($message);
                } catch (\Exception $e) {
                    $fcmMessage = " (FCM gagal dikirim: " . $e->getMessage() . ")";
                }
            } else {
                $fcmMessage = " (FCM gagal: berkas service-account.json tidak ditemukan)";
            }
        } else {
            $fcmMessage = " (FCM tidak dikirim karena Token FCM kosong)";
        }
 
        return redirect()->route('admin.violations.index')->with('success', "Akses ujian siswa '{$violation->student_name}' berhasil dibuka kembali (Pardoned)." . $fcmMessage);
    }
}
