<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\FcmRegistration;
use Illuminate\Http\Request;
 
class StudentController extends Controller
{
    /**
     * Display a listing of registered students (with search and pagination)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $query = FcmRegistration::query();
 
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('android_id', 'like', "%{$search}%")
                  ->orWhere('topic', 'like', "%{$search}%");
            });
        }
 
        $students = $query->with('latestViolation')->orderBy('registration_timestamp', 'desc')->paginate(15);
 
        return view('admin.students.index', compact('students', 'search'));
    }
 
    /**
     * Update the specified student registration details
     */
    public function update(Request $request, $id)
    {
        $student = FcmRegistration::findOrFail($id);
 
        $validated = $request->validate([
            'full_name'  => 'required|string|max:255',
            'points'     => 'required|integer|min:0',
        ]);
 
        $student->update($validated);
 
        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil diperbarui.');
    }
 
    /**
     * Remove the specified student registration
     */
    public function destroy($id)
    {
        $student = FcmRegistration::findOrFail($id);
        $student->delete();
 
        return redirect()->route('admin.students.index')->with('success', 'Registrasi perangkat siswa berhasil dihapus.');
    }
}
