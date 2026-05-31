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
 
        $query = CbtPelanggaran::query()
            ->leftJoin('fcm_registrations', 'cbt_pelanggaran.fcm_token', '=', 'fcm_registrations.fcm_token')
            ->select('cbt_pelanggaran.*', 'fcm_registrations.android_id');
 
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('cbt_pelanggaran.student_name', 'like', "%{$search}%")
                  ->orWhere('cbt_pelanggaran.reason', 'like', "%{$search}%")
                  ->orWhere('cbt_pelanggaran.device_model', 'like', "%{$search}%");
            });
        }
 
        if ($statusFilter !== 'ALL') {
            $query->where('cbt_pelanggaran.status', $statusFilter);
        }
 
        if ($statusFilter === 'UNBANNED') {
            $violationsData = $query->orderBy('cbt_pelanggaran.created_at', 'desc')->get();
            
            $grouped = [];
            foreach ($violationsData as $row) {
                // Grouping berdasarkan android_id perangkat, fallback ke student_name jika token tidak terdaftar
                $identifier = $row->android_id ?: $row->student_name;
                
                if (!isset($grouped[$identifier])) {
                    $parent = clone $row;
                    $parent->total_bans = 0;
                    $parent->history = [];
                    $grouped[$identifier] = $parent;
                }
                
                $grouped[$identifier]->total_bans++;
                $historyItem = clone $row;
                $history = $grouped[$identifier]->history;
                $history[] = $historyItem;
                $grouped[$identifier]->history = $history;
            }
            
            // Manual pagination untuk collection hasil grouping
            $violationsCollection = collect(array_values($grouped));
            
            $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
            $perPage = 15;
            $currentPageItems = $violationsCollection->slice(($currentPage - 1) * $perPage, $perPage)->all();
            
            $violations = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentPageItems,
                $violationsCollection->count(),
                $perPage,
                $currentPage,
                ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
            );
        } else {
            $violations = $query->orderBy('cbt_pelanggaran.created_at', 'desc')->paginate(15);
        }
 
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
            if (!class_exists('\Kreait\Firebase\Factory')) {
                $fcmMessage = " (FCM tidak terkirim: Library Firebase PHP SDK belum terinstall di hosting. Silakan jalankan 'composer install' di terminal SSH hosting Anda)";
            } else {
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

                        $message = \Kreait\Firebase\Messaging\CloudMessage::fromArray([
                            'token' => $fcmToken,
                            'data' => [
                                'action' => 'UNBAN_STUDENT'
                            ]
                        ]);

                        $messaging->send($message);
                    } catch (\Throwable $e) {
                        $fcmMessage = " (FCM gagal dikirim: " . $e->getMessage() . ")";
                    }
                } else {
                    $fcmMessage = " (FCM gagal: berkas service-account.json tidak ditemukan)";
                }
            }
        } else {
            $fcmMessage = " (FCM tidak dikirim karena Token FCM kosong)";
        }
 
        return redirect()->route('admin.violations.index')->with('success', "Akses ujian siswa '{$violation->student_name}' berhasil dibuka kembali (Pardoned)." . $fcmMessage);
    }

    /**
     * Manually lift multiple student bans (Pardon)
     */
    public function bulkUnban(Request $request)
    {
        $idsString = $request->input('ids');
        if (empty($idsString)) {
            return redirect()->route('admin.violations.index')->with('error', 'Tidak ada siswa yang dipilih.');
        }

        $ids = explode(',', $idsString);
        $violations = CbtPelanggaran::whereIn('id', $ids)->where('status', 'BANNED')->get();

        if ($violations->isEmpty()) {
            return redirect()->route('admin.violations.index')->with('error', 'Tidak ada data siswa terpilih yang berstatus BANNED.');
        }

        $serviceAccountPath = base_path('service-account.json');
        if (!file_exists($serviceAccountPath)) {
            $legacyPath = base_path('../apkcbt.mtsn11majalengka.sch.id/administrator/service-account.json');
            if (file_exists($legacyPath)) {
                $serviceAccountPath = $legacyPath;
            }
        }

        $messaging = null;
        $fcmInstalled = class_exists('\Kreait\Firebase\Factory');
        if ($fcmInstalled && file_exists($serviceAccountPath)) {
            try {
                $factory = (new \Kreait\Firebase\Factory)->withServiceAccount($serviceAccountPath);
                $messaging = $factory->createMessaging();
            } catch (\Throwable $e) {
                // Ignore initialization errors
            }
        }

        $successCount = 0;
        $fcmSuccessCount = 0;
        $fcmFailCount = 0;

        foreach ($violations as $violation) {
            $violation->update([
                'status' => 'UNBANNED'
            ]);
            $successCount++;

            $fcmToken = $violation->fcm_token;
            if (!empty($fcmToken) && $messaging) {
                try {
                    $message = \Kreait\Firebase\Messaging\CloudMessage::fromArray([
                        'token' => $fcmToken,
                        'data' => [
                            'action' => 'UNBAN_STUDENT'
                        ]
                    ]);
                    $messaging->send($message);
                    $fcmSuccessCount++;
                } catch (\Throwable $e) {
                    $fcmFailCount++;
                }
            }
        }

        $fcmMessage = "";
        if (!$fcmInstalled) {
            $fcmMessage = " (FCM tidak terkirim: Library Firebase PHP SDK belum terinstall)";
        } elseif (!file_exists($serviceAccountPath)) {
            $fcmMessage = " (FCM tidak terkirim: berkas service-account.json tidak ditemukan)";
        } else {
            $fcmMessage = " (FCM terkirim: {$fcmSuccessCount} sukses" . ($fcmFailCount > 0 ? ", {$fcmFailCount} gagal" : "") . ")";
        }

        return redirect()->route('admin.violations.index')->with('success', "Akses ujian {$successCount} siswa berhasil dibuka kembali." . $fcmMessage);
    }

    /**
     * Delete a violation record (only if status is UNBANNED)
     */
    public function destroy($id)
    {
        $violation = CbtPelanggaran::findOrFail($id);

        if ($violation->status !== 'UNBANNED') {
            return redirect()->route('admin.violations.index')->with('error', 'Hanya riwayat pelanggaran yang sudah terlepas ban (UNBANNED) yang dapat dihapus.');
        }

        $violation->delete();

        return redirect()->route('admin.violations.index')->with('success', 'Riwayat pelanggaran berhasil dihapus.');
    }
}
