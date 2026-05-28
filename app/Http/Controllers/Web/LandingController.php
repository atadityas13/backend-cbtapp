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
        'AdityAs13xCBTAppMTsN11Majalengka_V422', // Android v4.2.2
        'AdityAs13xCBTAppMTsN11Majalengka_V423', // Android v4.2.3
        'AdityAs13xCBTAppMTsN11Majalengka',      // Versi lama (diizinkan masuk agar muncul alert update)
        'cbt-exam-browser',
        'CBTAppMTsN11Majalengka',
        'CBT-App-PC/1.0',
    ];

    /**
     * GET /
     * Gerbang utama: periksa jam operasional, validasi UA, lalu arahkan.
     */
    public function index(Request $request)
    {
        $downloadLink = Setting::getValue('download_url', 'https://play.google.com/store/apps/details?id=com.mtsn11.cbtapp');

        // ─── Pemeriksaan Rentang Tanggal & Jam Operasional ───
        $now = new \DateTime('now', new \DateTimeZone('Asia/Jakarta'));

        $operationalStart = Setting::getValue('operational_start_date', '2026-01-01 00:00:00');
        $operationalEnd   = Setting::getValue('operational_end_date',   '2026-12-31 23:59:59');
        $dailyStartHour   = (int) Setting::getValue('daily_start_hour',   7);
        $dailyStartMin    = (int) Setting::getValue('daily_start_minute', 0);
        $dailyEndHour     = (int) Setting::getValue('daily_end_hour',     17);
        $dailyEndMin      = (int) Setting::getValue('daily_end_minute',   0);

        $startDt = new \DateTime($operationalStart, new \DateTimeZone('Asia/Jakarta'));
        $endDt   = new \DateTime($operationalEnd,   new \DateTimeZone('Asia/Jakarta'));

        $isOpen      = false;
        $statusMsg   = 'Server Sedang Ditutup';
        $statusDetail= 'Ujian belum dimulai atau sudah berakhir.';
        $targetTime  = '';

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

        // ─── Jika server TUTUP: tampilkan halaman maintenance ───
        if (! $isOpen) {
            return view('web.maintenance', compact(
                'statusMsg', 'statusDetail', 'targetTime', 'downloadLink'
            ));
        }

        // ─── Jika server BUKA: validasi User-Agent ───
        $userAgent = $request->header('User-Agent', '');

        if (empty($userAgent) || ! $this->isValidUA($userAgent)) {
            return $this->showBlockedPage($downloadLink);
        }

        // UA valid → arahkan ke terminal verifikasi keamanan
        return redirect()->route('verify-security');
    }

    /**
     * GET /verify-security
     * Tampilkan terminal CLI retro (screen pinning trigger, audio beep, dll.).
     */
    public function verifySecurity(Request $request)
    {
        $userAgent     = $request->header('User-Agent', '');
        $downloadLink  = Setting::getValue('download_url', 'https://play.google.com/store/apps/details?id=com.mtsn11.cbtapp');

        $isNewAndroidApp = str_contains($userAgent, 'AdityAs13xCBTAppMTsN11Majalengka_V422')
                        || str_contains($userAgent, 'AdityAs13xCBTAppMTsN11Majalengka_V423');
        $isDesktopVersion = str_contains($userAgent, 'CBT-App-PC/1.0');

        return view('web.verify_security', compact(
            'isNewAndroidApp', 'isDesktopVersion', 'downloadLink'
        ));
    }

    /**
     * GET /portal
     * Halaman pemilihan akses asesmen (sumatif & madrasah).
     */
    public function portal(Request $request)
    {
        $userAgent    = $request->header('User-Agent', '');
        $downloadLink = Setting::getValue('download_url', 'https://play.google.com/store/apps/details?id=com.mtsn11.cbtapp');

        // Validasi ulang UA — cegah bypass langsung ke /portal via browser
        if (empty($userAgent) || ! $this->isValidUA($userAgent)) {
            return $this->showBlockedPage($downloadLink);
        }

        // Deteksi versi lama untuk warning popup
        $allowedApp    = 'AdityAs13xCBTAppMTsN11Majalengka_V422';
        $newAllowedApp = 'AdityAs13xCBTAppMTsN11Majalengka_V423';
        $oldApp        = 'AdityAs13xCBTAppMTsN11Majalengka';
        $cbtExamBrowser= 'cbt-exam-browser';

        $showAlert    = false;
        $alertMessage = '';

        if (str_contains($userAgent, $oldApp)
            && ! str_contains($userAgent, $allowedApp)
            && ! str_contains($userAgent, $newAllowedApp)
        ) {
            $showAlert    = true;
            $alertMessage = 'Versi aplikasi Anda sudah usang. Silakan unduh versi terbaru v4.2.3 untuk dapat mengikuti ujian.';
        } elseif (str_contains($userAgent, $cbtExamBrowser)) {
            $showAlert    = true;
            $alertMessage = 'Anda terdeteksi menggunakan CBT Exam Browser. Kami rekomendasikan menggunakan Aplikasi resmi CBT App.';
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
