<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
 
class MediaController extends Controller
{
    /**
     * Display a listing of uploaded media files with legacy synchronization
     */
    public function index()
    {
        // 1. Sinkronisasi dari folder lama (jika ada)
        $this->syncLegacyMedia();

        $laravelBase = public_path('uploads');
        
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

        return view('admin.media.index', compact('uploadedImageList', 'uploadedAudioList'));
    }

    /**
     * Safely delete a media file
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'file_name' => 'required|string',
            'type'      => 'required|in:image,audio',
        ]);

        $fileName = basename($validated['file_name']); // Menghindari directory traversal attack
        $type = $validated['type'];

        $laravelBase = public_path('uploads');
        $fullPath = ($type === 'audio') 
            ? $laravelBase . '/audio/' . $fileName 
            : $laravelBase . '/' . $fileName;

        if (File::exists($fullPath)) {
            if (File::delete($fullPath)) {
                return redirect()->route('admin.media.index')->with('success', 'File berhasil dihapus!');
            }
            return redirect()->route('admin.media.index')->with('error', 'Gagal menghapus file.');
        }

        return redirect()->route('admin.media.index')->with('error', 'File tidak ditemukan.');
    }

    /**
     * Sync legacy media files by copying them to Laravel public path
     */
    private function syncLegacyMedia()
    {
        $legacyBase = base_path('../apkcbt.mtsn11majalengka.sch.id/administrator/uploads');
        $laravelBase = public_path('uploads');
        
        if (!File::exists($laravelBase)) {
            File::makeDirectory($laravelBase, 0777, true, true);
        }
        if (!File::exists($laravelBase . '/audio')) {
            File::makeDirectory($laravelBase . '/audio', 0777, true, true);
        }
        
        if (File::isDirectory($legacyBase)) {
            // Salin Gambar dari folder legacy
            $files = File::files($legacyBase);
            foreach ($files as $file) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $dest = $laravelBase . '/' . $file->getFilename();
                    if (!File::exists($dest)) {
                        File::copy($file->getRealPath(), $dest);
                    }
                }
            }
            
            // Salin Audio dari folder legacy
            $legacyAudio = $legacyBase . '/audio';
            if (File::isDirectory($legacyAudio)) {
                $files = File::files($legacyAudio);
                foreach ($files as $file) {
                    if (strtolower($file->getExtension()) === 'mp3') {
                        $dest = $laravelBase . '/audio/' . $file->getFilename();
                        if (!File::exists($dest)) {
                            File::copy($file->getRealPath(), $dest);
                        }
                    }
                }
            }
        }
    }
}
