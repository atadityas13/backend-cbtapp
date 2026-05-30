<?php

namespace App\Jobs;

use App\Models\CbtScheduledNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Messaging\ApnsConfig;

class SendScheduledNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $now = Carbon::now('Asia/Jakarta');
        $currentTime = $now->format('H:i');
        $currentDay = $now->format('l'); // e.g. 'Saturday'

        Log::info("Running SendScheduledNotificationJob at {$now->toDateTimeString()} for Time: {$currentTime}, Day: {$currentDay}");

        // Find active schedules that match the current time
        $schedules = CbtScheduledNotification::where('is_active', true)
            ->where('schedule_time', $currentTime)
            ->get();

        if ($schedules->isEmpty()) {
            return;
        }

        foreach ($schedules as $schedule) {
            $days = $schedule->days_of_week ?? [];

            // Verify if schedule is active on the current day
            if (!in_array('ALL', $days) && !in_array($currentDay, $days)) {
                Log::info("Schedule ID {$schedule->id} matches time {$currentTime} but is not scheduled for {$currentDay}. Skip.");
                continue;
            }

            // Check if already sent today to avoid double sending
            if ($schedule->last_sent_at && $schedule->last_sent_at->timezone('Asia/Jakarta')->isToday()) {
                Log::info("Schedule ID {$schedule->id} was already sent today. Skip.");
                continue;
            }

            try {
                // 1. Process Sequential Message (Round-Robin)
                $titles = explode('|', $schedule->judul);
                $descriptions = explode('|', $schedule->deskripsi);
                
                $totalOptions = count($titles);
                $currentIndex = intval($schedule->last_sent_index ?? 0);
                
                if ($totalOptions > 1) {
                    $judul = trim($titles[$currentIndex % $totalOptions]);
                    
                    // Match description at same index, fallback to index 0 if not set
                    $descIndex = isset($descriptions[$currentIndex]) ? $currentIndex : 0;
                    $deskripsi = trim($descriptions[$descIndex]);
                    
                    $nextIndex = ($currentIndex + 1) % $totalOptions;
                } else {
                    $judul = trim($schedule->judul);
                    $deskripsi = trim($schedule->deskripsi);
                    $nextIndex = 0;
                }

                // 2. Process Smart Shuffle Audio (No Consecutive Repeat)
                $sounds = $schedule->custom_sound ?? ['default'];
                if (!is_array($sounds)) {
                    $sounds = [$sounds];
                }
                if (empty($sounds)) {
                    $sounds = ['default'];
                }

                $chosenSound = 'default';
                if (count($sounds) > 1) {
                    $lastSentSound = $schedule->last_sent_sound;
                    // Filter out the sound played yesterday
                    $availableSounds = array_filter($sounds, function($s) use ($lastSentSound) {
                        return trim($s) !== trim($lastSentSound);
                    });
                    
                    // Fallback to all if somehow all got filtered out
                    if (empty($availableSounds)) {
                        $availableSounds = $sounds;
                    }
                    
                    $randomKey = array_rand($availableSounds);
                    $chosenSound = $availableSounds[$randomKey];
                } else if (!empty($sounds)) {
                    $chosenSound = $sounds[0];
                }

                Log::info("Sending Scheduled Notification ID {$schedule->id}: Message option {$currentIndex}/{$totalOptions} -> '{$judul}' with sound: '{$chosenSound}'");

                // Dispatch
                $this->sendNotification($schedule, $judul, $deskripsi, $chosenSound);

                // Update last sent timestamp & states
                $schedule->update([
                    'last_sent_at' => Carbon::now(),
                    'last_sent_index' => $nextIndex,
                    'last_sent_sound' => $chosenSound,
                ]);
                Log::info("Successfully sent and updated last_sent_at, next_index={$nextIndex}, sound={$chosenSound} for Schedule ID {$schedule->id}");
            } catch (\Throwable $e) {
                Log::error("Failed to send scheduled notification ID {$schedule->id}: " . $e->getMessage(), [
                    'exception' => $e
                ]);
            }
        }
    }

    /**
     * Send the actual Firebase Cloud Message payload
     */
    private function sendNotification(CbtScheduledNotification $schedule, string $judul, string $deskripsi, string $chosenSound): void
    {
        if (app()->environment('testing')) {
            Log::info("[TEST MODE] Mocked Firebase send successfully for Schedule ID: {$schedule->id}");
            return;
        }

        $topik = $schedule->topik ?? '';
        $fcmTokenTarget = $schedule->fcm_token ?? '';
        $gambarUrl = $schedule->gambar_url ?? '';
        $link = $schedule->link ?? '';
        $prioritas = strtoupper($schedule->prioritas ?? 'HIGH');
        $category = $schedule->category ?? 'normal';
        $duration = strval($schedule->duration ?? '30');

        // Setup sound details
        $soundUrl = '';
        $soundInternal = 'default';

        if (!empty($chosenSound)) {
            if (filter_var($chosenSound, FILTER_VALIDATE_URL)) {
                $soundUrl = $chosenSound;
                $soundInternal = 'default';
            } else {
                $soundInternal = $chosenSound;
                $soundUrl = '';
            }
        }

        // Setup Firebase Factory
        $serviceAccountPath = base_path('service-account.json');
        if (!file_exists($serviceAccountPath)) {
            $legacyPath = base_path('../apkcbt.mtsn11majalengka.sch.id/administrator/service-account.json');
            if (file_exists($legacyPath)) {
                $serviceAccountPath = $legacyPath;
            } else {
                throw new \Exception('File service-account.json tidak ditemukan di root project!');
            }
        }

        $factory = (new Factory)->withServiceAccount($serviceAccountPath);
        $messaging = $factory->createMessaging();

        // Construct notification payload with all standard rich parameters
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

        if (!empty($fcmTokenTarget)) {
            // Send to specific student token
            $message = CloudMessage::fromArray([
                'token' => $fcmTokenTarget,
                'data' => $dataPayload,
                'android' => $androidConfig,
                'webpush' => $webpushConfig,
                'apns' => $apnsConfig,
            ]);
            $messaging->send($message);
        } else {
            // Send to global or custom topic
            $targetValue = !empty($topik) ? $topik : 'cbt_notif';
            $message = CloudMessage::fromArray([
                'topic' => $targetValue,
                'data' => $dataPayload,
                'android' => $androidConfig,
                'webpush' => $webpushConfig,
                'apns' => $apnsConfig,
            ]);
            $messaging->send($message);
        }
    }
}
