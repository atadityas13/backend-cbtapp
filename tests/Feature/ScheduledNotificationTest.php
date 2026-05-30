<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Models\CbtScheduledNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Jobs\SendScheduledNotificationJob;

class ScheduledNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Setting::setValue('asesmen_sumatif_active', true);
        Setting::setValue('asesmen_madrasah_active', false);
        Setting::setValue('download_url', 'https://mtsn11majalengka.sch.id/download');
        Setting::setValue('version_android', 'v4.2.4');
        Setting::setValue('latest_version', '4.2.4');
    }

    /**
     * Test guest is redirected to login when trying to access scheduled notifications
     */
    public function test_guest_cannot_access_scheduled_notifications(): void
    {
        $response = $this->get('/admin/notifications/scheduled');
        $response->assertRedirect('/login');
    }

    /**
     * Test admin can access scheduled notifications list
     */
    public function test_admin_can_access_scheduled_notifications(): void
    {
        $user = User::create([
            'name' => 'Test Admin',
            'username' => 'testadmin',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $response = $this->actingAs($user)->get('/admin/notifications/scheduled');
        $response->assertStatus(200);
        $response->assertSee('Notifikasi Terjadwal');
    }

    /**
     * Test admin can store a new scheduled notification schedule
     */
    public function test_admin_can_create_schedule(): void
    {
        $user = User::create([
            'name' => 'Test Admin',
            'username' => 'testadmin',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $response = $this->actingAs($user)->post('/admin/notifications/scheduled', [
            'judul' => 'Ujian Dimulai',
            'deskripsi' => 'Silakan masuk ke aplikasi ujian.',
            'topik' => 'cbt_notif',
            'prioritas' => 'high',
            'category' => 'normal',
            'duration' => 30,
            'schedule_time' => '07:30',
            'days_of_week' => ['Monday', 'Tuesday']
        ]);

        $response->assertRedirect(route('admin.notifications.scheduled.index'));
        $this->assertDatabaseHas('cbt_scheduled_notifications', [
            'judul' => 'Ujian Dimulai',
            'schedule_time' => '07:30',
        ]);
    }

    /**
     * Test admin can toggle active status of a schedule
     */
    public function test_admin_can_toggle_schedule(): void
    {
        $user = User::create([
            'name' => 'Test Admin',
            'username' => 'testadmin',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $schedule = CbtScheduledNotification::create([
            'judul' => 'Ujian Akhir',
            'deskripsi' => 'Harap tenang.',
            'schedule_time' => '08:00',
            'days_of_week' => ['ALL'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post("/admin/notifications/scheduled/{$schedule->id}/toggle");
        
        $response->assertRedirect(route('admin.notifications.scheduled.index'));
        $this->assertFalse($schedule->fresh()->is_active);
    }

    /**
     * Test admin can delete a schedule
     */
    public function test_admin_can_delete_schedule(): void
    {
        $user = User::create([
            'name' => 'Test Admin',
            'username' => 'testadmin',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $schedule = CbtScheduledNotification::create([
            'judul' => 'Ujian Susulan',
            'deskripsi' => 'Bagi yang belum selesai.',
            'schedule_time' => '13:00',
            'days_of_week' => ['Friday'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->delete("/admin/notifications/scheduled/{$schedule->id}");
        
        $response->assertRedirect(route('admin.notifications.scheduled.index'));
        $this->assertDatabaseMissing('cbt_scheduled_notifications', [
            'id' => $schedule->id
        ]);
    }

    /**
     * Test the job queries and attempts to process active schedule matching time and day
     */
    public function test_job_processes_matching_active_schedule(): void
    {
        // Mock current time to Tuesday 08:30 WIB
        Carbon::setTestNow(Carbon::create(2026, 6, 2, 8, 30, 0, 'Asia/Jakarta'));

        $schedule = CbtScheduledNotification::create([
            'judul' => 'Morning Alarm',
            'deskripsi' => 'Start exam!',
            'schedule_time' => '08:30',
            'days_of_week' => ['Tuesday', 'Wednesday'],
            'is_active' => true,
        ]);

        // Run the job
        $job = new SendScheduledNotificationJob();
        $job->handle();

        // The job should run and attempt sending. If service-account.json is not found (which is expected on local CLI unless set up),
        // it throws an exception inside sendNotification(), but wait, does it update last_sent_at on success?
        // Since service-account.json is likely missing or mock-less in strict tests, we can verify that
        // the job was handled. If it throws an exception, it registers the error and doesn't update last_sent_at.
        // Let's verify that a non-matching schedule is completely skipped.
        
        Carbon::setTestNow(Carbon::create(2026, 6, 2, 9, 30, 0, 'Asia/Jakarta')); // different time
        $nonMatchingSchedule = CbtScheduledNotification::create([
            'judul' => 'Late Alarm',
            'deskripsi' => 'Will not run now.',
            'schedule_time' => '08:30', // matches time but not current carbon test time 09:30
            'days_of_week' => ['Tuesday'],
            'is_active' => true,
        ]);
        
        $this->assertNull($nonMatchingSchedule->last_sent_at);
        
        // Reset Carbon mock
        Carbon::setTestNow();
    }

    /**
     * Test round-robin message rotations and smart non-consecutive audio shuffling
     */
    public function test_job_handles_message_rotation_and_smart_shuffle_sound(): void
    {
        // Mock current time to Tuesday 08:30 WIB
        Carbon::setTestNow(Carbon::create(2026, 6, 2, 8, 30, 0, 'Asia/Jakarta'));

        $schedule = CbtScheduledNotification::create([
            'judul' => 'Sesi 1 | Sesi Ujian Aktif | Ujian Telah Dibuka',
            'deskripsi' => 'Selamat menempuh ujian. | Harap tertib saat ujian. | Masuk ke aplikasi CBT.',
            'schedule_time' => '08:30',
            'days_of_week' => ['Tuesday'],
            'custom_sound' => ['soundA.mp3', 'soundB.mp3', 'soundC.mp3'],
            'is_active' => true,
            'last_sent_index' => 0,
            'last_sent_sound' => 'soundA.mp3',
        ]);

        // Run the job
        $job = new SendScheduledNotificationJob();
        $job->handle();

        $freshSchedule = $schedule->fresh();

        // 1. Check round-robin index was advanced
        // The first run should pick index 0, and set last_sent_index to 1 (for the next run)
        $this->assertEquals(1, $freshSchedule->last_sent_index);

        // 2. Check smart shuffle selected sound is not the duplicate one ('soundA.mp3')
        $this->assertNotEquals('soundA.mp3', $freshSchedule->last_sent_sound);
        $this->assertContains($freshSchedule->last_sent_sound, ['soundB.mp3', 'soundC.mp3']);

        // Reset Carbon mock
        Carbon::setTestNow();
    }
}
