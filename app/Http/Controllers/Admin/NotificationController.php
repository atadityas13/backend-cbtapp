<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\FcmRegistration;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Exception\Messaging\MessagingException;
 
class NotificationController extends Controller
{
    public function index()
    {
        // Fetch all unique topics for dropdown
        $topics = FcmRegistration::distinct()->pluck('topic')->toArray();
        if (empty($topics)) {
            $topics = ['cbt_notif'];
        }
        
        // Fetch all students for specific token targeting
        $students = FcmRegistration::orderBy('full_name', 'asc')->get();

        $laravelBase = public_path('uploads');
        
        $uploadedImageList = [];
        if (\Illuminate\Support\Facades\File::exists($laravelBase)) {
            $files = \Illuminate\Support\Facades\File::files($laravelBase);
            foreach ($files as $file) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $uploadedImageList[] = $file->getFilename();
                }
            }
        }

        $uploadedAudioList = [];
        $audioDir = $laravelBase . '/audio';
        if (\Illuminate\Support\Facades\File::exists($audioDir)) {
            $files = \Illuminate\Support\Facades\File::files($audioDir);
            foreach ($files as $file) {
                if (strtolower($file->getExtension()) === 'mp3') {
                    $uploadedAudioList[] = $file->getFilename();
                }
            }
        }
 
        return view('admin.notifications.index', compact('topics', 'students', 'uploadedImageList', 'uploadedAudioList'));
    }
 
    /**
     * Process and trigger the Firebase push notification
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'judul'               => 'required|string|max:255',
            'deskripsi'           => 'required|string',
            'topik'               => 'nullable|string',
            'fcm_token'           => 'nullable|string',
            'gambar_url'          => 'nullable|string',
            'link'                => 'nullable|string',
            'prioritas'           => 'required|in:high,normal',
            'custom_sound'        => 'nullable|string',
            'jeda_pengiriman'     => 'nullable|integer|min:0',
            'category'            => 'required|string|max:50',
            'duration'            => 'nullable|integer|min:5|max:300',
        ]);
 
        $judul = $validated['judul'];
        $deskripsi = $validated['deskripsi'];
        $topik = $validated['topik'] ?? '';
        $fcmTokenTarget = $validated['fcm_token'] ?? '';
        $gambarUrl = $validated['gambar_url'] ?? '';
        $link = $validated['link'] ?? '';
        $prioritas = strtoupper($validated['prioritas']);
        $customSound = $validated['custom_sound'] ?? '';
        $jedaPengiriman = intval($validated['jeda_pengiriman'] ?? 0);
        $category = $validated['category'];
        $duration = strval($validated['duration'] ?? '30');
 
        // 1. Logika Upload Audio (Jika Ada)
        $soundUrl = '';
        $soundInternal = 'default';
 
        if ($request->hasFile('audio_file') && $request->file('audio_file')->isValid()) {
            $audioFile = $request->file('audio_file');
            $safeName = time() . "_voice_" . preg_replace("/[^a-zA-Z0-9\._-]/", "_", $audioFile->getClientOriginalName());
            $audioFile->move(public_path('uploads/audio'), $safeName);
            $soundUrl = asset('uploads/audio/' . $safeName);
            $soundInternal = 'default';
        } elseif (!empty($customSound)) {
            if (filter_var($customSound, FILTER_VALIDATE_URL)) {
                $soundUrl = $customSound;
                $soundInternal = 'default';
            } else {
                $soundInternal = $customSound;
                $soundUrl = '';
            }
        }
 
        // 2. Logika Upload Gambar (Jika Ada)
        if (empty($gambarUrl) && $request->hasFile('gambar_file') && $request->file('gambar_file')->isValid()) {
            $imageFile = $request->file('gambar_file');
            $safeName = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "_", $imageFile->getClientOriginalName());
            $imageFile->move(public_path('uploads'), $safeName);
            $gambarUrl = asset('uploads/' . $safeName);
        }
 
        // 3. Setup Firebase Factory
        // The service account is safely located in the storage/app or project root!
        $serviceAccountPath = base_path('service-account.json');
        if (!file_exists($serviceAccountPath)) {
            // Fallback to reading legacy location or display clear error
            $legacyPath = base_path('../apkcbt.mtsn11majalengka.sch.id/administrator/service-account.json');
            if (file_exists($legacyPath)) {
                $serviceAccountPath = $legacyPath;
            } else {
                return redirect()->back()->withInput()->with('error', 'File service-account.json tidak ditemukan di root project! Silakan unggah terlebih dahulu.');
            }
        }
 
        try {
            $factory = (new Factory)->withServiceAccount($serviceAccountPath);
            $messaging = $factory->createMessaging();
 
            // 4. Construct Notification Payload
            $dataPayload = [
                'title'     => $judul,
                'message'   => $deskripsi,
                'image'     => $gambarUrl ?: '',
                'link'      => $link ?: '',
                'sound'     => $soundInternal,
                'sound_url' => $soundUrl ?: '',
                'priority'  => $prioritas,
                'category'  => $category,
                'duration'  => $duration,
            ];
 
            $androidConfig = AndroidConfig::fromArray([
                'priority' => ($prioritas === 'HIGH') ? 'high' : 'normal',
            ]);
 
            $webpushConfig = WebPushConfig::fromArray([
                'notification' => [
                    'title' => $judul,
                    'body'  => $deskripsi,
                    'icon'  => $gambarUrl ?: '',
                    'image' => $gambarUrl ?: '',
                ],
                'fcm_options' => ['link' => $link ?: ''],
            ]);
 
            $apnsConfig = ApnsConfig::fromArray([
                'payload' => [
                    'aps' => [
                        'alert' => ['title' => $judul, 'body' => $deskripsi],
                        'sound' => $soundInternal !== 'default' ? $soundInternal : 'default',
                    ],
                    'link'  => $link ?: '',
                    'image' => $gambarUrl ?: '',
                ],
            ]);
 
            // 5. Trigger Sending
            if ($jedaPengiriman > 0 && !empty($topik) && empty($fcmTokenTarget)) {
                // Bulk sending with delay
                $tokens = FcmRegistration::where('topic', $topik)->pluck('fcm_token')->toArray();
                
                if (empty($tokens)) {
                    return redirect()->back()->withInput()->with('error', "Tidak ada perangkat terdaftar di topik '{$topik}'.");
                }
 
                foreach ($tokens as $idx => $token) {
                    $message = CloudMessage::withTarget('token', $token)
                        ->withAndroidConfig($androidConfig)
                        ->withWebPushConfig($webpushConfig)
                        ->withApnsConfig($apnsConfig)
                        ->withData($dataPayload);
                    
                    $messaging->send($message);
                    if ($idx < count($tokens) - 1) {
                        sleep($jedaPengiriman);
                    }
                }
                
                return redirect()->route('admin.notifications.index')->with('success', 'Notifikasi massal berhasil dikirim ke ' . count($tokens) . ' perangkat.');
            } else {
                // Standard sending to single token OR topic directly (instant)
                $targetType = !empty($fcmTokenTarget) ? 'token' : 'topic';
                $targetValue = !empty($fcmTokenTarget) ? $fcmTokenTarget : $topik;
 
                if (empty($targetValue)) {
                    return redirect()->back()->withInput()->with('error', 'Silakan pilih target Topik atau Token Siswa!');
                }
 
                $message = CloudMessage::withTarget($targetType, $targetValue)
                    ->withAndroidConfig($androidConfig)
                    ->withWebPushConfig($webpushConfig)
                    ->withApnsConfig($apnsConfig)
                    ->withData($dataPayload);
 
                $messaging->send($message);
                
                return redirect()->route('admin.notifications.index')->with('success', 'Notifikasi berhasil dikirim.');
            }
 
        } catch (MessagingException $e) {
            return redirect()->back()->withInput()->with('error', 'Firebase Error: ' . $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
