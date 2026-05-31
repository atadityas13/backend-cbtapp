<?php

namespace Tests\Feature;

use App\Models\CbtBantuanProktor;
use App\Models\FcmRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CbtHelpTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test sending help requests and validation constraints
     */
    public function test_student_can_send_help_request_once(): void
    {
        // Send a valid help request
        $response = $this->postJson('/api/help/send', [
            'android_id'  => 'DEVICE123',
            'username'    => 'student01',
            'nama_siswa'  => 'Ahmad Dani',
            'pesan_siswa' => 'Saya tidak bisa memuat gambar soal nomor 10.'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success'
        ]);

        $this->assertDatabaseHas('cbt_bantuan_proktor', [
            'android_id'  => 'DEVICE123',
            'pesan_siswa' => 'Saya tidak bisa memuat gambar soal nomor 10.',
            'status'      => 'PENDING'
        ]);

        // Attempting to send again while PENDING should fail (Locking Constraint)
        $duplicateResponse = $this->postJson('/api/help/send', [
            'android_id'  => 'DEVICE123',
            'username'    => 'student01',
            'nama_siswa'  => 'Ahmad Dani',
            'pesan_siswa' => 'Tolong pak dibalas.'
        ]);

        $duplicateResponse->assertStatus(400);
        $duplicateResponse->assertJson([
            'status' => 'error'
        ]);
    }

    /**
     * Test checking help status at different states
     */
    public function test_student_can_check_help_status(): void
    {
        // 1. Idle state (no request)
        $response1 = $this->postJson('/api/help/status', [
            'android_id' => 'DEVICE123'
        ]);
        $response1->assertStatus(200);
        $response1->assertJson([
            'status' => 'idle'
        ]);

        // 2. Pending state
        CbtBantuanProktor::create([
            'android_id'  => 'DEVICE123',
            'username'    => 'student01',
            'nama_siswa'  => 'Ahmad Dani',
            'pesan_siswa' => 'Pertanyaan saya...',
            'status'      => 'PENDING'
        ]);

        $response2 = $this->postJson('/api/help/status', [
            'android_id' => 'DEVICE123'
        ]);
        $response2->assertStatus(200);
        $response2->assertJson([
            'status' => 'pending'
        ]);

        // 3. Answered state
        CbtBantuanProktor::where('android_id', 'DEVICE123')
            ->update([
                'status'          => 'ANSWERED',
                'balasan_proktor' => 'Harap refresh halaman.'
            ]);

        $response3 = $this->postJson('/api/help/status', [
            'android_id' => 'DEVICE123'
        ]);
        $response3->assertStatus(200);
        $response3->assertJson([
            'status' => 'answered',
            'reply'  => 'Harap refresh halaman.'
        ]);
    }

    /**
     * Test proctor replying to help tickets in admin dashboard
     */
    public function test_proctor_can_reply_to_help_request(): void
    {
        $admin = User::create([
            'name'     => 'Test Proctor',
            'username' => 'testproctor',
            'password' => bcrypt('password'),
            'role'     => 'proktor'
        ]);

        $help = CbtBantuanProktor::create([
            'android_id'  => 'DEVICE123',
            'username'    => 'student01',
            'nama_siswa'  => 'Ahmad Dani',
            'pesan_siswa' => 'Gambar mati.',
            'status'      => 'PENDING'
        ]);

        // Create FCM Registration to simulate push notification token
        FcmRegistration::create([
            'android_id' => 'DEVICE123',
            'full_name'  => 'Ahmad Dani',
            'fcm_token'  => 'FAKE_TOKEN_XYZ',
            'topic'      => 'cbt_notif'
        ]);

        $response = $this->actingAs($admin)
            ->post("/admin/help/{$help->id}/reply", [
                'balasan_proktor' => 'Silakan ketuk ikon sinyal untuk me-refresh.'
            ]);

        $response->assertRedirect(route('admin.help.index'));
        
        $this->assertDatabaseHas('cbt_bantuan_proktor', [
            'id'              => $help->id,
            'balasan_proktor' => 'Silakan ketuk ikon sinyal untuk me-refresh.',
            'status'          => 'ANSWERED'
        ]);
    }

    /**
     * Test student confirming the reply to unlock new requests
     */
    public function test_student_can_confirm_resolved_to_unlock(): void
    {
        CbtBantuanProktor::create([
            'android_id'      => 'DEVICE123',
            'username'        => 'student01',
            'nama_siswa'      => 'Ahmad Dani',
            'pesan_siswa'     => 'Error',
            'balasan_proktor' => 'Coba lagi.',
            'status'          => 'ANSWERED'
        ]);

        // Confirming help resolved
        $response = $this->postJson('/api/help/confirm', [
            'android_id' => 'DEVICE123'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success'
        ]);

        $this->assertDatabaseHas('cbt_bantuan_proktor', [
            'android_id' => 'DEVICE123',
            'status'     => 'RESOLVED'
        ]);

        // Student should now be able to send a new request (Unblocked)
        $newRequestResponse = $this->postJson('/api/help/send', [
            'android_id'  => 'DEVICE123',
            'username'    => 'student01',
            'nama_siswa'  => 'Ahmad Dani',
            'pesan_siswa' => 'Pertanyaan baru setelah keluhan awal beres.'
        ]);

        $newRequestResponse->assertStatus(200);
    }
}
