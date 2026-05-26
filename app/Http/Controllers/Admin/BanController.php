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
 
        return redirect()->route('admin.violations.index')->with('success', "Akses ujian siswa '{$violation->student_name}' berhasil dibuka kembali (Pardoned).");
    }
}
