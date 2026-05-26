<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\CbtPelanggaran;
use App\Models\FcmRegistration;
use App\Models\Setting;
use Illuminate\Http\Request;
 
class DashboardController extends Controller
{
    public function index()
    {
        // 1. Compute Statistics
        $totalDevices = FcmRegistration::count();
        $activeBans = CbtPelanggaran::where('status', 'BANNED')->count();
        $totalViolations = CbtPelanggaran::count();
        
        $sumatifActive = (bool) Setting::getValue('asesmen_sumatif_active', true);
        $madrasahActive = (bool) Setting::getValue('asesmen_madrasah_active', false);
        $activeAssessment = $sumatifActive ? 'Asesmen Sumatif' : ($madrasahActive ? 'Asesmen Madrasah' : 'Tidak Aktif');
 
        // 2. Fetch last 5 active violations for real-time log feed
        $recentViolations = CbtPelanggaran::orderBy('created_at', 'desc')->limit(5)->get();
 
        return view('admin.dashboard', compact(
            'totalDevices',
            'activeBans',
            'totalViolations',
            'activeAssessment',
            'recentViolations'
        ));
    }
}
