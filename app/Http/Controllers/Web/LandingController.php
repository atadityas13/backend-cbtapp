<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Daftar User-Agent resmi yang diizinkan mengakses portal ujian.
     */
    private array $allowedUAs = [
        'ATADevLabs_CBTAppMTsN11Majalengka', // Android v4.2.4 (TERBARU)
        'CBT-App-PC/1.0',                    // Aplikasi Windows
    ];

    /**
     * Header secret yang dikirim oleh WebView Android (ExamScreen.kt)
     */
    private const CBT_SECRET = 'AdityAs13_CBTApp_MTsN11Majalengka';

    /**
     * Helper: cek apakah server saat ini sedang buka berdasarkan pengaturan jam & tanggal.
     * Dipanggil di semua endpoint agar tidak bisa di-bypass langsung via URL.
     */
    private function checkOperational(): array
    {
        $now = new \DateTime('now', new \DateTimeZone('Asia/Jakarta'));

        $operationalStart = Setting::getValue('operational_start_date', '2026-01-01 00:00:00');
        $operationalEnd   = Setting::getValue('operational_end_date',   '2026-12-31 23:59:59');
        $dailyStartHour   = (int) Setting::getValue('daily_start_hour',   7);
        $dailyStartMin    = (int) Setting::getValue('daily_start_minute', 0);
        $dailyEndHour     = (int) Setting::getValue('daily_end_hour',     17);
        $dailyEndMin      = (int) Setting::getValue('daily_end_minute',   0);

        $startDt = new \DateTime($operationalStart, new \DateTimeZone('Asia/Jakarta'));
        $endDt   = new \DateTime($operationalEnd,   new \DateTimeZone('Asia/Jakarta'));

        $isOpen       = false;
        $statusMsg    = 'Server Sedang Ditutup';
        $statusDetail = 'Ujian belum dimulai atau sudah berakhir.';
        $targetTime   = '';

        if ($now >= $startDt && $now <= $endDt) {
            $currentMins = (int)$now->format('H') * 60 + (int)$now->format('i');
            $startMins   = $dailyStartHour * 60 + $dailyStartMin;
            $endMins     = $dailyEndHour   * 60 + $dailyEndMin;

            if ($currentMins >= $startMins && $currentMins <= $endMins) {
                $isOpen = true;
            } else {
                $statusMsg    = 'Server Sedang Ditutup';
                $statusDetail = 'Server ujian akan dibuka kembali dalam :';

                $target = clone $now;
                if ($currentMins > $endMins) {
                    $target->modify('+1 day');
                }
                $target->setTime($dailyStartHour, $dailyStartMin, 0);
                $targetTime = $target->format('Y-m-d H:i:s');
            }
        } else {
            if ($now < $startDt) {
                $statusMsg    = 'Server Belum Dibuka';
                $statusDetail = 'Server ujian akan dibuka kembali dalam :';
                $target       = clone $startDt;
                $target->setTime($dailyStartHour, $dailyStartMin, 0);
                $targetTime   = $target->format('Y-m-d H:i:s');
            } else {
                $statusMsg    = 'Periode Ujian Selesai';
                $statusDetail = 'Terima kasih telah mengikuti ujian.';
            }
        }

        return compact('isOpen', 'statusMsg', 'statusDetail', 'targetTime');
    }

    /**
     * GET /
     * Gerbang utama: periksa jam operasional, validasi UA, lalu arahkan.
     */
    public function index(Request $request)
    {
        $downloadLink = Setting::getValue('download_url', 'https://play.google.com/store/apps/details?id=com.mtsn11.cbtapp');

        $operational = $this->checkOperational();

        if (! $operational['isOpen']) {
            return view('web.maintenance', [
                'statusMsg'   => $operational['statusMsg'],
                'statusDetail'=> $operational['statusDetail'],
                'targetTime'  => $operational['targetTime'],
                'downloadLink'=> $downloadLink,
            ]);
        }

        $userAgent = $request->header('User-Agent', '');

        if (empty($userAgent) || ! $this->isValidUA($userAgent)) {
            return $this->showBlockedPage($downloadLink);
        }

        return redirect()->route('verify-security');
    }

    /**
     * GET /verify-security
     * Terminal CLI verifikasi keamanan (screen pinning, audio beep, dll.).
     * FIX: Cek jam operasional agar tidak bisa diakses langsung saat server tutup.
     */
    public function verifySecurity(Request $request)
    {
        $downloadLink = Setting::getValue('download_url', 'https://play.google.com/store/apps/details?id=com.mtsn11.cbtapp');

        // FIX: Jika server tutup, kembalikan ke halaman maintenance
        $operational = $this->checkOperational();
        if (! $operational['isOpen']) {
            return view('web.maintenance', [
                'statusMsg'   => $operational['statusMsg'],
                'statusDetail'=> $operational['statusDetail'],
                'targetTime'  => $operational['targetTime'],
                'downloadLink'=> $downloadLink,
            ]);
        }

        $userAgent = $request->header('User-Agent', '');

        $isNewAndroidApp = str_contains($userAgent, 'ATADevLabs_CBTAppMTsN11Majalengka');
        $isDesktopVersion = str_contains($userAgent, 'CBT-App-PC/1.0');

        return view('web.verify_security', compact(
            'isNewAndroidApp', 'isDesktopVersion', 'downloadLink'
        ));
    }

    /**
     * GET /portal
     * Halaman pemilihan akses asesmen (sumatif & madrasah).
     * FIX: Tambah cek jam operasional agar tidak bisa di-bypass via URL langsung.
     */
    public function portal(Request $request)
    {
        $downloadLink = Setting::getValue('download_url', 'https://play.google.com/store/apps/details?id=com.mtsn11.cbtapp');

        // FIX: Cek jam operasional juga di portal
        $operational = $this->checkOperational();
        if (! $operational['isOpen']) {
            return view('web.maintenance', [
                'statusMsg'   => $operational['statusMsg'],
                'statusDetail'=> $operational['statusDetail'],
                'targetTime'  => $operational['targetTime'],
                'downloadLink'=> $downloadLink,
            ]);
        }

        $userAgent = $request->header('User-Agent', '');

        if (empty($userAgent) || ! $this->isValidUA($userAgent)) {
            return $this->showBlockedPage($downloadLink);
        }

        // Deteksi versi untuk warning popup
        $showAlert    = false;
        $alertMessage = '';

        if (str_contains($userAgent, 'ATADevLabs_CBTAppMTsN11Majalengka')) {
            // Versi terbaru — tidak ada alert
        }

        $sumatifActive  = (bool) Setting::getValue('asesmen_sumatif_active', true);
        $madrasahActive = (bool) Setting::getValue('asesmen_madrasah_active', false);

        return view('web.landing', compact(
            'sumatifActive', 'madrasahActive',
            'showAlert', 'alertMessage', 'downloadLink'
        ));
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function isValidUA(string $userAgent): bool
    {
        foreach ($this->allowedUAs as $ua) {
            if (str_contains($userAgent, $ua)) {
                return true;
            }
        }
        return false;
    }

    private function showBlockedPage(string $downloadLink)
    {
        return response()->view('web.blocked', compact('downloadLink'), 403);
    }
}
