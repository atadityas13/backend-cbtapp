<?php
 
namespace App\Http\Controllers\Web;
 
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
 
class LandingController extends Controller
{
    public function index(Request $request)
    {
        $userAgent = $request->header('User-Agent', '');
 
        // Allowed User-Agent constants
        $allowedApp = "AdityAs13xCBTAppMTsN11Majalengka_V422"; // We also support V423 in version 6
        $newAllowedApp = "AdityAs13xCBTAppMTsN11Majalengka_V423";
        $oldApp = "AdityAs13xCBTAppMTsN11Majalengka"; 
        $cbtExamBrowser = "cbt-exam-browser";
        $otherExamBrowser = "CBTAppMTsN11Majalengka";
        $cbtAppPC = "CBT-App-PC/1.0";
        $downloadLink = Setting::getValue('download_url', 'https://mtsn11majalengka.sch.id/download');
 
        if (empty($userAgent)) {
            return $this->showBlockedPage($downloadLink);
        }
 
        $allowedUAs = [$allowedApp, $newAllowedApp, $oldApp, $cbtExamBrowser, $cbtAppPC, $otherExamBrowser];
        $isValidUA = false;
        foreach ($allowedUAs as $ua) {
            if (str_contains($userAgent, $ua)) {
                $isValidUA = true;
                break;
            }
        }
 
        if (!$isValidUA) {
            return $this->showBlockedPage($downloadLink);
        }
 
        // Check for old version warnings
        $showAlert = false;
        $alertMessage = "";
        
        // If they open using older app (without _V422 or _V423)
        if (str_contains($userAgent, $oldApp) && !str_contains($userAgent, $allowedApp) && !str_contains($userAgent, $newAllowedApp)) {
            $showAlert = true;
            $alertMessage = "Versi aplikasi Anda sudah usang. Silakan unduh versi terbaru v4.2.3 untuk dapat mengikuti ujian.";
        } elseif (str_contains($userAgent, $cbtExamBrowser)) {
            $showAlert = true;
            $alertMessage = "Anda terdeteksi menggunakan CBT Exam Browser, kami rekomendasikan menggunakan Aplikasi resmi CBT App.";
        }
 
        $sumatifActive = (bool) Setting::getValue('asesmen_sumatif_active', true);
        $madrasahActive = (bool) Setting::getValue('asesmen_madrasah_active', false);
 
        return view('web.landing', compact(
            'sumatifActive',
            'madrasahActive',
            'showAlert',
            'alertMessage',
            'downloadLink'
        ));
    }
 
    private function showBlockedPage($downloadLink)
    {
        return response()->view('web.blocked', compact('downloadLink'), 403);
    }
}
