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
                Log::info("Sending Scheduled Notification ID {$schedule->id}: '{$schedule->judul}'");
                $this->sendNotification($schedule);

                // Update last sent timestamp
                $schedule->update([
                    'last_sent_at' => Carbon::now(),
                ]);
                Log::info("Successfully sent and updated last_sent_at for Schedule ID {$schedule->id}");
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
    private function sendNotification(CbtScheduledNotification $schedule): void
    {
        $judul = $schedule->judul;
        $deskripsi = $schedule->deskripsi;
        $topik = $schedule->topik ?? '';
        $fcmTokenTarget = $schedule->fcm_token ?? '';
        $gambarUrl = $schedule->gambar_url ?? '';
        $link = $schedule->link ?? '';
        $prioritas = strtoupper($schedule->prioritas ?? 'HIGH');
        $customSound = $schedule->custom_sound ?? '';
        $category = $schedule->category ?? 'normal';
        $duration = strval($schedule->duration ?? '30');

        // Setup sound details
        $soundUrl = '';
        $soundInternal = 'default';

        if (!empty($customSound)) {
            if (filter_var($customSound, FILTER_VALIDATE_URL)) {
                $soundUrl = $customSound;
                $soundInternal = 'default';
            } else {
                $soundInternal = $customSound;
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
